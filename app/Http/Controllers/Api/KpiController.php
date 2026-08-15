<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Goal;
use App\Models\Kpi;
use App\Models\KpiPlan;
use App\Services\ApprovalWorkflowService;
use App\Services\MetricAggregationService;
use App\Services\RecordAttachmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class KpiController extends Controller
{
    public function __construct(
        private readonly ApprovalWorkflowService $workflowService,
        private readonly MetricAggregationService $metrics,
        private readonly RecordAttachmentService $attachments,
    ) {
    }

    public function index(Request $request)
    {
        Gate::authorize('viewAny', KpiPlan::class);

        $user = $request->user();
        $query = KpiPlan::query()->with([
            'kpis',
            'goal',
            'manager:id,name,username,role',
            'attachments',
        ]);

        if (!$user->isCEO()) {
            $query->where(function ($scope) use ($user): void {
                $scope->where('division', $user->divisionKey())
                    ->orWhereHas('goal', fn ($goal) => $goal->where('division', $user->divisionKey()));
            });

            if ($user->isStaff()) {
                $query->where('status', 'approved');
            } else {
                $query->where(function ($plans) use ($user): void {
                    $plans->where('manager_id', $user->id)
                        ->orWhere('status', 'approved');
                });
            }
        }

        return response()->json(
            $query->orderByDesc('id')->get()->map(
                fn (KpiPlan $plan): array => $this->formatPlan($plan, $user),
            ),
        );
    }

    public function storePlan(Request $request)
    {
        Gate::authorize('create', KpiPlan::class);

        $validated = $this->validatePlan($request);
        $user = $request->user();
        $goal = isset($validated['goal_id'])
            ? Goal::query()->findOrFail($validated['goal_id'])
            : null;
        $division = $user->divisionKey();
        $planTitle = trim((string) ($validated['title'] ?? $goal?->title ?? ''));

        if ($goal && ($goal->status !== 'active' || $goal->division !== $division)) {
            throw ValidationException::withMessages([
                'goal_id' => 'Goal tidak aktif atau bukan milik divisi Anda.',
            ]);
        }

        $hasPendingPlan = $goal && KpiPlan::query()
            ->where('goal_id', $goal->id)
            ->where('manager_id', $user->id)
            ->where('status', 'pending_ceo')
            ->exists();

        if ($hasPendingPlan) {
            throw ValidationException::withMessages([
                'goal_id' => 'Masih ada rencana KPI untuk goal ini yang menunggu keputusan CEO.',
            ]);
        }

        $plan = DB::transaction(function () use ($validated, $goal, $user, $division, $planTitle): KpiPlan {
            $plan = KpiPlan::query()->create([
                'goal_id' => $goal?->id,
                'title' => $planTitle,
                'division' => $division,
                'manager_id' => $user->id,
                'status' => 'pending_ceo',
                'score' => 0,
            ]);

            $this->replaceKpis($plan, $validated['kpis']);

            return $plan;
        });

        if ($request->hasFile('supporting_file')) {
            $this->attachments->store(
                $plan,
                $request->file('supporting_file'),
                $user,
                'kpi_proposal',
            );
        }

        $approval = $this->workflowService->createRequest(
            type: 'kpi_plan',
            subject: $plan,
            requester: $user,
            division: $division,
            initialApprover: null,
            payload: $this->approvalPayload($plan, $goal),
        );

        return response()->json([
            'success' => true,
            'message' => 'Rencana KPI berhasil diajukan kepada CEO.',
            'plan' => $this->formatPlan(
                $plan->fresh(['kpis', 'goal', 'manager', 'attachments']),
                $user,
            ),
            'approval_id' => $approval->id,
        ], 201);
    }

    public function updatePlan(Request $request, KpiPlan $kpiPlan)
    {
        Gate::authorize('update', $kpiPlan);
        $validated = $this->validatePlan($request);
        $actor = $request->user();
        $goal = isset($validated['goal_id'])
            ? Goal::query()->findOrFail($validated['goal_id'])
            : null;

        if ($goal && ($goal->status !== 'active' || $goal->division !== $actor->divisionKey())) {
            throw ValidationException::withMessages([
                'goal_id' => 'Goal tidak aktif atau bukan milik divisi Anda.',
            ]);
        }

        $wasRejected = $kpiPlan->status === 'rejected';
        DB::transaction(function () use ($kpiPlan, $validated, $goal, $actor): void {
            $kpiPlan->forceFill([
                'goal_id' => $goal?->id,
                'title' => trim((string) ($validated['title'] ?? $goal?->title ?? '')),
                'division' => $actor->divisionKey(),
                'status' => 'pending_ceo',
                'score' => 0,
            ])->save();
            $this->replaceKpis($kpiPlan, $validated['kpis']);
        });

        if ($request->hasFile('supporting_file')) {
            $this->attachments->store(
                $kpiPlan,
                $request->file('supporting_file'),
                $actor,
                'kpi_proposal',
            );
        }

        $approval = $kpiPlan->approvalRequest()
            ->whereIn('status', ['pending_ceo'])
            ->latest('id')
            ->first();

        if ($approval) {
            $approval->forceFill([
                'payload' => $this->approvalPayload($kpiPlan, $goal),
                'submitted_at' => now(),
            ])->save();
        } else {
            $approval = $this->workflowService->createRequest(
                type: 'kpi_plan',
                subject: $kpiPlan,
                requester: $actor,
                division: $actor->divisionKey(),
                initialApprover: null,
                payload: $this->approvalPayload($kpiPlan, $goal),
            );
        }

        return response()->json([
            'success' => true,
            'message' => $wasRejected
                ? 'Rencana KPI telah direvisi dan diajukan kembali kepada CEO.'
                : 'Rencana KPI menunggu CEO berhasil diperbarui.',
            'plan' => $this->formatPlan(
                $kpiPlan->fresh(['kpis', 'goal', 'manager', 'attachments']),
                $actor,
            ),
            'approval_id' => $approval->id,
        ]);
    }

    public function updateScore(Request $request, Kpi $kpi)
    {
        $kpi->load('plan.kpis');
        $plan = $kpi->plan;
        $actor = $request->user();

        abort_unless(
            $plan
                && $plan->status === 'approved'
                && ($actor->isCEO() || $plan->manager_id === $actor->id),
            403,
            'Hanya manager pemilik atau CEO yang dapat mengisi realisasi KPI yang sudah disahkan.',
        );
        if ($kpi->aggregation_type !== 'manual') {
            throw ValidationException::withMessages([
                'current_value' => 'Kolom realisasi KPI ini dihitung otomatis dari sumber data dan tidak dapat diisi manual.',
            ]);
        }

        $validated = $request->validate([
            'current_value' => ['required', 'numeric', 'min:0'],
        ]);
        $kpi->forceFill(['current_value' => $validated['current_value']])->save();
        $this->metrics->recalculateKpi($kpi->fresh('plan.goal'));

        return response()->json([
            'success' => true,
            'message' => 'Nilai realisasi KPI dan seluruh statistik terkait telah dihitung ulang.',
            'plan' => $this->formatPlan(
                $plan->fresh(['kpis', 'goal', 'manager', 'attachments']),
                $actor,
            ),
        ]);
    }

    private function validatePlan(Request $request): array
    {
        if (is_string($request->input('kpis'))) {
            $decoded = json_decode((string) $request->input('kpis'), true);
            if (is_array($decoded)) {
                $request->merge(['kpis' => $decoded]);
            }
        }

        $validated = $request->validate([
            'goal_id' => ['nullable', 'integer', 'exists:goals,id'],
            'title' => ['nullable', 'string', 'max:255', 'required_without:goal_id'],
            'kpis' => ['required', 'array', 'min:1'],
            'kpis.*.title' => ['required', 'string', 'max:255'],
            'kpis.*.target_value' => ['required', 'numeric', 'gt:0'],
            'kpis.*.unit' => ['required', 'string', 'max:50'],
            'kpis.*.weight' => ['required', 'numeric', 'gt:0', 'max:100'],
            'kpis.*.direction' => ['required', 'in:higher_is_better,lower_is_better'],
            'kpis.*.aggregation_type' => ['required', 'in:count,sum,average,percentage,manual'],
            'kpis.*.data_source' => ['required', 'in:tasks,leads,client_inflows,attendance,manual'],
            'supporting_file' => ['nullable', 'file', 'max:10240', 'mimes:pdf,doc,docx,xls,xlsx,csv,txt,jpg,jpeg,png,webp,zip'],
        ]);

        $totalWeight = collect($validated['kpis'])->sum(fn ($kpi) => (float) $kpi['weight']);
        if (abs($totalWeight - 100) > 0.001) {
            throw ValidationException::withMessages([
                'kpis' => "Total bobot KPI harus tepat 100%. Total saat ini {$totalWeight}%.",
            ]);
        }

        return $validated;
    }

    private function replaceKpis(KpiPlan $plan, array $rows): void
    {
        $plan->kpis()->delete();
        foreach ($rows as $kpiData) {
            Kpi::query()->create([
                ...collect($kpiData)->except('current_value')->all(),
                'kpi_plan_id' => $plan->id,
                'current_value' => 0,
            ]);
        }
    }

    private function approvalPayload(KpiPlan $plan, ?Goal $goal): array
    {
        return [
            'goal_id' => $goal?->id,
            'goal_title' => $goal?->title,
            'plan_title' => $plan->title,
            'proposal_source' => $goal ? 'ceo_goal' : 'manager_initiative',
            'total_weight' => 100,
            'kpi_count' => $plan->kpis()->count(),
            'edited_at' => now()->toIso8601String(),
        ];
    }

    private function formatPlan(KpiPlan $plan, $viewer): array
    {
        $canEdit = $viewer->id === $plan->manager_id
            && in_array($plan->status, ['pending_ceo', 'rejected'], true);
        $canScore = $plan->status === 'approved'
            && ($viewer->isCEO() || $viewer->id === $plan->manager_id);

        return [
            'id' => $plan->id,
            'goal_id' => $plan->goal_id,
            'title' => $plan->title,
            'division' => $plan->divisionKey(),
            'manager_id' => $plan->manager_id,
            'manager' => $plan->manager,
            'goal' => $plan->goal,
            'status' => $plan->status,
            'score' => (float) $plan->score,
            'can_edit' => $canEdit,
            'can_score' => $canScore,
            'edit_lock_reason' => $canEdit ? null : 'Rencana KPI yang sudah disahkan terkunci; buat revisi baru melalui approval.',
            'kpis' => $plan->kpis->map(function (Kpi $kpi) use ($canScore): array {
                $target = (float) $kpi->target_value;
                $current = (float) $kpi->current_value;
                $achievement = $target <= 0
                    ? 0
                    : ($kpi->direction === 'lower_is_better'
                        ? ($current <= $target ? 100 : min(100, ($target / max($current, 0.0001)) * 100))
                        : min(100, max(0, ($current / $target) * 100)));

                return [
                    'id' => $kpi->id,
                    'title' => $kpi->title,
                    'target_value' => $target,
                    'current_value' => $current,
                    'unit' => $kpi->unit,
                    'weight' => (float) $kpi->weight,
                    'direction' => $kpi->direction,
                    'aggregation_type' => $kpi->aggregation_type,
                    'data_source' => $kpi->data_source,
                    'achievement' => round($achievement, 2),
                    'weighted_score' => round($achievement * ((float) $kpi->weight / 100), 2),
                    'can_score' => $canScore && $kpi->aggregation_type === 'manual',
                ];
            })->values(),
            'attachments' => $plan->attachments->map(fn ($attachment): array => [
                'id' => $attachment->id,
                'name' => $attachment->original_name,
                'size_bytes' => $attachment->size_bytes,
                'download_url' => route('record-attachments.download', $attachment),
            ])->values(),
        ];
    }
}
