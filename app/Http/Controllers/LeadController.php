<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\User;
use App\Services\DataDeletionRequestService;
use App\Services\MetricAggregationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class LeadController extends Controller
{
    private const STATUSES = ['leads', 'penawaran', 'deal', 'lost'];

    public function __construct(
        private readonly MetricAggregationService $metrics,
        private readonly DataDeletionRequestService $deletions,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorizeCrm($request);

        $leads = $this->leadQuery()->get();

        return response()->json(
            $leads->map(fn (Lead $lead): array => $this->serializeLead($lead, $request->user()))->values(),
        );
    }

    public function overview(Request $request): JsonResponse
    {
        $this->authorizeCrm($request);

        $leads = $this->leadQuery()->get();
        $won = $leads->where('status', 'deal');
        $lost = $leads->where('status', 'lost');
        $open = $leads->whereIn('status', ['leads', 'penawaran']);
        $closedCount = $won->count() + $lost->count();

        $sourceRows = $leads
            ->groupBy(fn (Lead $lead): string => $lead->source ?: 'Tidak diketahui')
            ->map(function (Collection $sourceLeads, string $source): array {
                return [
                    'source' => $source,
                    'leads' => $sourceLeads->count(),
                    'won' => $sourceLeads->where('status', 'deal')->count(),
                    'pipeline_value' => (float) $sourceLeads
                        ->whereIn('status', ['leads', 'penawaran'])
                        ->sum('project_value'),
                    'actual_revenue' => (float) $sourceLeads->sum(
                        fn (Lead $lead): float => (float) ($lead->actual_revenue ?? 0),
                    ),
                ];
            })
            ->sortByDesc('leads')
            ->values();

        $funnel = collect(self::STATUSES)->map(fn (string $status): array => [
            'status' => $status,
            'count' => $leads->where('status', $status)->count(),
            'value' => (float) $leads->where('status', $status)->sum('project_value'),
        ]);

        $dueFollowUps = $open->filter(
            fn (Lead $lead): bool => $lead->next_follow_up_at
                && $lead->next_follow_up_at->lessThanOrEqualTo(now()),
        );

        return response()->json([
            'summary' => [
                'total_leads' => $leads->count(),
                'open_leads' => $open->count(),
                'won_leads' => $won->count(),
                'lost_leads' => $lost->count(),
                'whatsapp_leads' => $leads->where('source', 'WhatsApp')->count(),
                'due_follow_ups' => $dueFollowUps->count(),
                'conversion_rate' => $closedCount > 0
                    ? round(($won->count() / $closedCount) * 100, 1)
                    : 0,
                'pipeline_value' => (float) $open->sum('project_value'),
                'weighted_forecast' => (float) $open->sum(
                    fn (Lead $lead): float => (float) $lead->project_value
                        * ($lead->status === 'penawaran' ? 0.5 : 0.15),
                ),
                'won_value' => (float) $won->sum('project_value'),
                'actual_revenue' => (float) $leads->sum(
                    fn (Lead $lead): float => (float) ($lead->actual_revenue ?? 0),
                ),
            ],
            'sources' => $sourceRows,
            'funnel' => $funnel,
            'due_follow_ups' => $dueFollowUps
                ->sortBy('next_follow_up_at')
                ->take(10)
                ->map(fn (Lead $lead): array => $this->serializeLead($lead, $request->user()))
                ->values(),
        ]);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $this->authorizeCrm($request);
        $lead = $this->findLead($id);

        $lead->load([
            'assignee:id,username,name',
            'activities' => fn ($query) => $query->with('user:id,username,name')->limit(50),
        ])->loadSum('clientInflows as actual_revenue', 'payment_amount');

        return response()->json([
            'lead' => $this->serializeLead($lead, $request->user()),
            'activities' => $lead->activities->map(fn (LeadActivity $activity): array => [
                'id' => $activity->id,
                'type' => $activity->type,
                'channel' => $activity->channel,
                'direction' => $activity->direction,
                'body' => $activity->body,
                'occurred_at' => $activity->occurred_at?->toIso8601String(),
                'user' => $activity->user ? [
                    'name' => $activity->user->name,
                    'username' => $activity->user->username,
                ] : null,
            ])->values(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorizeCrm($request);

        $validated = $request->validate($this->leadRules());
        $phone = $this->normalizePhone($validated['phone'] ?? null);

        if ($phone) {
            $existing = Lead::query()
                ->where('phone', $phone)
                ->whereIn('status', ['leads', 'penawaran'])
                ->latest('id')
                ->first();

            if ($existing) {
                $existing->fill(array_filter([
                    'email' => $validated['email'] ?? null,
                    'domicile' => $validated['domicile'] ?? null,
                    'campaign' => $validated['campaign'] ?? null,
                    'next_follow_up_at' => $validated['next_follow_up_at'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                ], fn ($value): bool => $value !== null && $value !== ''))->save();

                if (! empty($validated['initial_message'])) {
                    $this->recordActivity(
                        $existing,
                        $request->user(),
                        $validated['initial_message'],
                        $existing->source === 'WhatsApp' ? 'whatsapp' : 'internal',
                        'inbound',
                        'message',
                    );
                }

                $existing = $this->reloadLead($existing);

                return response()->json([
                    ...$this->serializeLead($existing, $request->user()),
                    'duplicate_merged' => true,
                    'message' => 'Nomor ini sudah memiliki lead aktif; informasi baru digabungkan.',
                ]);
            }
        }

        $assignee = $this->resolveAssignee($validated['username'] ?? null, $request->user());
        $projectValue = $this->projectValue($validated);
        $status = $validated['column'] ?? 'leads';

        $lead = Lead::query()->create([
            'client_name' => $validated['name'],
            'phone' => $phone,
            'email' => $validated['email'] ?? null,
            'domicile' => $validated['domicile'] ?? null,
            'project_value' => $projectValue,
            'budget_text' => $validated['budget'] ?? $this->budgetLabel($projectValue, $status),
            'status' => $status,
            'source' => $validated['source'] ?? 'WhatsApp',
            'campaign' => $validated['campaign'] ?? null,
            'type' => $validated['type'] ?? 'Pembangunan',
            'notes' => $validated['notes'] ?? null,
            'next_follow_up_at' => $validated['next_follow_up_at'] ?? null,
            'won_at' => $status === 'deal' ? now() : null,
            'assigned_to' => $assignee->id,
            'created_by' => $request->user()->id,
        ]);

        $this->recordActivity(
            $lead,
            $request->user(),
            $validated['initial_message'] ?? 'Lead dibuat di CRM.',
            ($validated['source'] ?? 'WhatsApp') === 'WhatsApp' ? 'whatsapp' : 'internal',
            ! empty($validated['initial_message']) ? 'inbound' : 'internal',
            ! empty($validated['initial_message']) ? 'message' : 'created',
        );

        $this->metrics->recalculateForDataSource('leads', $assignee->divisionKey());
        $lead = $this->reloadLead($lead);

        return response()->json(
            $this->serializeLead($lead, $request->user()),
            201,
        );
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $this->authorizeCrm($request);
        $lead = $this->findLead($id);
        abort_unless($this->canManage($lead, $request->user()), 403);

        $validated = $request->validate($this->leadRules(true));
        $beforeStatus = $lead->status;

        $lead->fill(array_filter([
            'client_name' => $validated['name'] ?? null,
            'phone' => array_key_exists('phone', $validated)
                ? $this->normalizePhone($validated['phone'])
                : null,
            'email' => $validated['email'] ?? null,
            'domicile' => $validated['domicile'] ?? null,
            'project_value' => array_key_exists('project_value', $validated)
                ? (float) $validated['project_value']
                : null,
            'budget_text' => $validated['budget'] ?? null,
            'source' => $validated['source'] ?? null,
            'campaign' => $validated['campaign'] ?? null,
            'type' => $validated['type'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'next_follow_up_at' => $validated['next_follow_up_at'] ?? null,
            'lost_reason' => $validated['lost_reason'] ?? null,
        ], fn ($value): bool => $value !== null));

        if (isset($validated['column'])) {
            $lead->status = $validated['column'];
            $this->applyStatusLifecycle($lead);
        }

        $lead->save();

        if ($beforeStatus !== $lead->status) {
            $this->recordStageChange($lead, $request->user(), $beforeStatus, $lead->status);
        }

        $this->metrics->recalculateForDataSource('leads', $lead->assignee?->divisionKey());
        $lead = $this->reloadLead($lead);

        return response()->json([
            'message' => 'Lead berhasil diperbarui.',
            'lead' => $this->serializeLead($lead, $request->user()),
        ]);
    }

    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $this->authorizeCrm($request);
        $lead = $this->findLead($id);
        abort_unless($this->canManage($lead, $request->user()), 403);

        $validated = $request->validate([
            'column' => ['required', Rule::in(self::STATUSES)],
            'budget' => ['nullable', 'string', 'max:100'],
            'lost_reason' => ['nullable', 'string', 'max:255'],
        ]);

        $beforeStatus = $lead->status;
        $lead->status = $validated['column'];
        if (array_key_exists('budget', $validated)) {
            $lead->budget_text = $validated['budget'];
        }
        if (array_key_exists('lost_reason', $validated)) {
            $lead->lost_reason = $validated['lost_reason'];
        }
        $this->applyStatusLifecycle($lead);
        $lead->save();

        if ($beforeStatus !== $lead->status) {
            $this->recordStageChange($lead, $request->user(), $beforeStatus, $lead->status);
        }

        $this->metrics->recalculateForDataSource('leads', $lead->assignee?->divisionKey());

        return response()->json([
            'success' => true,
            'lead' => $this->serializeLead($this->reloadLead($lead), $request->user()),
        ]);
    }

    public function storeActivity(Request $request, string $id): JsonResponse
    {
        $this->authorizeCrm($request);
        $lead = $this->findLead($id);
        abort_unless($this->canManage($lead, $request->user()), 403);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
            'type' => ['nullable', Rule::in(['message', 'call', 'meeting', 'note', 'follow_up'])],
            'channel' => ['nullable', Rule::in(['whatsapp', 'phone', 'meeting', 'email', 'internal'])],
            'direction' => ['nullable', Rule::in(['inbound', 'outbound', 'internal'])],
            'occurred_at' => ['nullable', 'date'],
            'next_follow_up_at' => ['nullable', 'date'],
        ]);

        $activity = $this->recordActivity(
            $lead,
            $request->user(),
            $validated['body'],
            $validated['channel'] ?? 'internal',
            $validated['direction'] ?? 'internal',
            $validated['type'] ?? 'note',
            $validated['occurred_at'] ?? null,
        );

        if (array_key_exists('next_follow_up_at', $validated)) {
            $lead->forceFill(['next_follow_up_at' => $validated['next_follow_up_at']])->save();
        }

        return response()->json([
            'message' => 'Aktivitas lead berhasil dicatat.',
            'activity' => [
                'id' => $activity->id,
                'type' => $activity->type,
                'channel' => $activity->channel,
                'direction' => $activity->direction,
                'body' => $activity->body,
                'occurred_at' => $activity->occurred_at?->toIso8601String(),
            ],
            'lead' => $this->serializeLead($this->reloadLead($lead), $request->user()),
        ], 201);
    }

    public function whatsappIntake(Request $request): JsonResponse
    {
        $this->authorizeCrm($request);

        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:32', 'regex:/^[+0-9().\-\s]{9,32}$/'],
            'name' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
            'campaign' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:100'],
            'occurred_at' => ['nullable', 'date'],
        ]);

        $phone = $this->normalizePhone($validated['phone']);
        $lead = Lead::query()
            ->where('phone', $phone)
            ->whereIn('status', ['leads', 'penawaran'])
            ->latest('id')
            ->first();
        $created = false;

        if (! $lead) {
            $created = true;
            $lead = Lead::query()->create([
                'client_name' => $validated['name'] ?: 'WhatsApp '.substr((string) $phone, -4),
                'phone' => $phone,
                'project_value' => 0,
                'budget_text' => 'Belum dikualifikasi',
                'status' => 'leads',
                'source' => 'WhatsApp',
                'campaign' => $validated['campaign'] ?? null,
                'type' => $validated['type'] ?? 'Belum dikualifikasi',
                'assigned_to' => $request->user()->id,
                'created_by' => $request->user()->id,
            ]);
        }

        $this->recordActivity(
            $lead,
            $request->user(),
            $validated['message'],
            'whatsapp',
            'inbound',
            'message',
            $validated['occurred_at'] ?? null,
        );
        $this->metrics->recalculateForDataSource('leads', 'marketing');

        return response()->json([
            'created' => $created,
            'message' => $created
                ? 'Pesan WhatsApp membuat lead baru.'
                : 'Pesan WhatsApp ditambahkan ke lead aktif.',
            'lead' => $this->serializeLead($this->reloadLead($lead), $request->user()),
        ], $created ? 201 : 200);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $this->authorizeCrm($request);
        $lead = $this->findLead($id);
        abort_unless($this->canManage($lead, $request->user()), 403);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
        ]);
        $result = $this->deletions->request(
            $request->user(),
            'lead',
            $lead->id,
            $validated['reason'],
        );

        return response()->json([
            'success' => true,
            ...$result,
        ], $result['approval'] ? 202 : 200);
    }

    private function leadQuery()
    {
        return Lead::query()
            ->with('assignee:id,username,name')
            ->withCount('activities')
            ->withSum('clientInflows as actual_revenue', 'payment_amount')
            ->latest('id');
    }

    private function reloadLead(Lead $lead): Lead
    {
        return $this->leadQuery()->whereKey($lead->id)->firstOrFail();
    }

    private function findLead(string $id): Lead
    {
        $numericId = preg_replace('/\D+/', '', $id);
        abort_if($numericId === '', 404);

        return Lead::query()->findOrFail((int) $numericId);
    }

    private function serializeLead(Lead $lead, User $viewer): array
    {
        $projectValue = (float) $lead->project_value;
        $actualRevenue = (float) ($lead->actual_revenue ?? 0);

        return [
            'id' => 'lead-'.$lead->id,
            'name' => $lead->client_name,
            'phone' => $lead->phone,
            'whatsapp_url' => $lead->phone ? 'https://wa.me/'.$lead->phone : null,
            'email' => $lead->email,
            'domicile' => $lead->domicile,
            'budget' => $lead->budget_text ?: $this->budgetLabel($projectValue, $lead->status),
            'project_value' => $projectValue,
            'actual_revenue' => $actualRevenue,
            'source' => $lead->source ?: 'Tidak diketahui',
            'campaign' => $lead->campaign,
            'type' => $lead->type ?: 'Lainnya',
            'column' => in_array($lead->status, self::STATUSES, true) ? $lead->status : 'leads',
            'notes' => $lead->notes,
            'lost_reason' => $lead->lost_reason,
            'next_follow_up_at' => $lead->next_follow_up_at?->toIso8601String(),
            'last_contacted_at' => $lead->last_contacted_at?->toIso8601String(),
            'first_response_at' => $lead->first_response_at?->toIso8601String(),
            'won_at' => $lead->won_at?->toIso8601String(),
            'date' => $lead->created_at?->format('Y-m-d') ?? 'Hari ini',
            'activities_count' => (int) ($lead->activities_count ?? 0),
            'assignee' => $lead->assignee ? [
                'name' => $lead->assignee->name,
                'username' => $lead->assignee->username,
            ] : null,
            'can_delete' => $this->canManage($lead, $viewer),
            'can_manage' => $this->canManage($lead, $viewer),
        ];
    }

    private function leadRules(bool $partial = false): array
    {
        $required = $partial ? 'sometimes' : 'required';

        return [
            'name' => [$required, 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32', 'regex:/^[+0-9().\-\s]{9,32}$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'domicile' => ['nullable', 'string', 'max:255'],
            'budget' => ['nullable', 'string', 'max:100'],
            'project_value' => ['nullable', 'numeric', 'min:0'],
            'source' => ['nullable', 'string', 'max:100'],
            'campaign' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:100'],
            'column' => ['nullable', Rule::in(self::STATUSES)],
            'username' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'initial_message' => ['nullable', 'string', 'max:5000'],
            'next_follow_up_at' => ['nullable', 'date'],
            'lost_reason' => ['nullable', 'string', 'max:255'],
        ];
    }

    private function projectValue(array $validated): float
    {
        if (array_key_exists('project_value', $validated) && $validated['project_value'] !== null) {
            return (float) $validated['project_value'];
        }

        $budget = strtolower((string) ($validated['budget'] ?? ''));
        $numeric = (float) preg_replace('/[^0-9.,]/', '', str_replace(',', '.', $budget));

        if (str_contains($budget, 'jt')) {
            return $numeric * 1_000_000;
        }

        if (preg_match('/\d\s*m(?:iliar)?\b/i', $budget) || str_contains($budget, 'miliar')) {
            return $numeric * 1_000_000_000;
        }

        return $numeric;
    }

    private function budgetLabel(float $amount, string $status): string
    {
        $prefix = $status === 'deal' ? 'Deal: ' : 'Est: ';

        if ($amount >= 1_000_000_000) {
            return $prefix.'Rp '.rtrim(rtrim(number_format($amount / 1_000_000_000, 1, '.', ''), '0'), '.').'M';
        }

        if ($amount >= 1_000_000) {
            return $prefix.'Rp '.rtrim(rtrim(number_format($amount / 1_000_000, 1, '.', ''), '0'), '.').'Jt';
        }

        return $prefix.'Rp '.number_format($amount, 0, ',', '.');
    }

    private function normalizePhone(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);
        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '0')) {
            $digits = '62'.substr($digits, 1);
        }

        return strlen($digits) >= 9 && strlen($digits) <= 15 ? $digits : null;
    }

    private function resolveAssignee(?string $username, User $actor): User
    {
        return User::query()->where('username', $username)->first()
            ?? ($actor->divisionKey() === 'marketing' ? $actor : null)
            ?? User::query()->where('role', 'mgr_marketing')->first()
            ?? User::query()->where('role', 'ceo')->first()
            ?? User::query()->firstOrFail();
    }

    private function recordActivity(
        Lead $lead,
        User $actor,
        string $body,
        string $channel,
        string $direction,
        string $type,
        ?string $occurredAt = null,
    ): LeadActivity {
        $activity = $lead->activities()->create([
            'user_id' => $actor->id,
            'type' => $type,
            'channel' => $channel,
            'direction' => $direction,
            'body' => $body,
            'occurred_at' => $occurredAt ?: now(),
        ]);

        $updates = ['last_contacted_at' => $activity->occurred_at];
        if ($direction === 'outbound' && ! $lead->first_response_at) {
            $updates['first_response_at'] = $activity->occurred_at;
        }
        $lead->forceFill($updates)->save();

        return $activity;
    }

    private function recordStageChange(Lead $lead, User $actor, string $from, string $to): void
    {
        $labels = [
            'leads' => 'Leads Masuk',
            'penawaran' => 'Penawaran',
            'deal' => 'Deal',
            'lost' => 'Lost',
        ];

        $this->recordActivity(
            $lead,
            $actor,
            sprintf('Tahap berubah dari %s menjadi %s.', $labels[$from] ?? $from, $labels[$to] ?? $to),
            'internal',
            'internal',
            'stage_change',
        );
    }

    private function applyStatusLifecycle(Lead $lead): void
    {
        if ($lead->status === 'deal') {
            $lead->won_at ??= now();
            $lead->lost_reason = null;

            return;
        }

        $lead->won_at = null;
    }

    private function authorizeCrm(Request $request): void
    {
        abort_unless(
            $request->user()?->isCEO() || $request->user()?->divisionKey() === 'marketing',
            403,
        );
    }

    private function canManage(Lead $lead, User $actor): bool
    {
        return $actor->isCEO()
            || $actor->id === $lead->assigned_to
            || ($lead->assignee && $actor->isManagerOf($lead->assignee));
    }
}
