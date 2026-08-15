<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectCost;
use App\Services\AccountingService;
use App\Services\ProjectCostingService;
use App\Services\RecordAttachmentService;
use App\Services\SecurityAuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProjectCostingController extends Controller
{
    public function __construct(
        private readonly ProjectCostingService $costing,
        private readonly AccountingService $accounting,
        private readonly RecordAttachmentService $attachments,
        private readonly SecurityAuditService $audit,
    ) {}

    public function index(Request $request)
    {
        $viewer = $this->authorizeRead($request);
        $projects = Project::query()
            ->with([
                'manager:id,name,username,job_title',
                'costs' => fn ($query) => $query
                    ->with([
                        'creator:id,name,username',
                        'attachments:id,attachable_type,attachable_id,original_name,mime_type,size_bytes',
                    ])
                    ->latest('cost_date')
                    ->latest('id'),
            ])
            ->orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END")
            ->orderByDesc('contract_value')
            ->get()
            ->map(fn (Project $project): array => [
                ...$project->toArray(),
                'summary' => $this->costing->summary($project),
            ]);

        return response()->json([
            'can_write' => $this->canWrite($viewer),
            'projects' => $projects,
            'portfolio' => [
                'contract_value' => round((float) $projects->sum('contract_value'), 2),
                'budget_cost' => round((float) $projects->sum('budget_cost'), 2),
                'actual_cost' => round((float) $projects->sum('summary.actual_cost'), 2),
                'estimated_margin' => round((float) $projects->sum('summary.estimated_margin'), 2),
                'design_count' => $projects->where('project_type', 'design')->count(),
                'contractor_count' => $projects->where('project_type', 'contractor')->count(),
            ],
            'server_time' => now()->toIso8601String(),
        ]);
    }

    public function store(Request $request)
    {
        $actor = $this->authorizeWrite($request);
        $validated = $this->validateProject($request);
        $validated['code'] = ($validated['code'] ?? null) ?: $this->nextProjectCode($validated['project_type']);
        $project = Project::query()->create($validated);

        $this->auditProject($request, $actor, $project, 'project.created');

        return response()->json([
            'success' => true,
            'message' => 'Proyek berhasil dibuat dan siap dipantau biaya serta marginnya.',
            'project' => [
                ...$project->fresh(['manager', 'costs'])->toArray(),
                'summary' => $this->costing->summary($project),
            ],
        ], 201);
    }

    public function update(Request $request, Project $project)
    {
        $actor = $this->authorizeWrite($request);
        $validated = $this->validateProject($request, $project);
        unset($validated['code']);
        $project->fill($validated)->save();

        $this->auditProject($request, $actor, $project, 'project.updated');

        return response()->json([
            'success' => true,
            'message' => 'Informasi proyek, anggaran, dan progres berhasil diperbarui.',
            'project' => [
                ...$project->fresh(['manager', 'costs'])->toArray(),
                'summary' => $this->costing->summary($project),
            ],
        ]);
    }

    public function storeCost(Request $request, Project $project)
    {
        $actor = $this->authorizeWrite($request);
        $validated = $request->validate([
            'cost_date' => ['required', 'date'],
            'category' => ['required', 'string', 'in:design_labor,consultant,material,contractor_labor,transport,permit,software,other'],
            'description' => ['required', 'string', 'max:1000'],
            'amount' => ['required', 'numeric', 'gt:0', 'max:999999999999.99'],
            'vendor' => ['nullable', 'string', 'max:255'],
            'attachment' => [
                'nullable',
                'file',
                'max:10240',
                'mimes:pdf,jpg,jpeg,png,webp,doc,docx,xls,xlsx,csv,txt,zip',
            ],
        ]);

        $cost = DB::transaction(function () use ($actor, $validated, $project, $request): ProjectCost {
            $cost = $project->costs()->create([
                ...collect($validated)->except('attachment')->all(),
                'created_by_id' => $actor->id,
            ]);
            $entry = $this->accounting->createEntry(
                $actor,
                $validated['cost_date'],
                "Biaya proyek {$project->code}: {$validated['description']}",
                [
                    [
                        'system_key' => 'direct_project_cost',
                        'debit' => $validated['amount'],
                        'project_id' => $project->id,
                    ],
                    [
                        'system_key' => 'cash_bank',
                        'credit' => $validated['amount'],
                        'project_id' => $project->id,
                    ],
                ],
                'project_cost',
                $cost->id,
                "PC-{$cost->id}",
            );
            $cost->forceFill(['journal_entry_id' => $entry->id])->save();
            if ($request->hasFile('attachment')) {
                $this->attachments->store($cost, $request->file('attachment'), $actor, 'cost_evidence');
            }

            return $cost;
        });

        $this->audit->record(
            'project.cost_posted',
            actor: $actor,
            request: $request,
            metadata: [
                'project' => $project->code,
                'amount' => (float) $cost->amount,
                'category' => $cost->category,
            ],
            subjectType: ProjectCost::class,
            subjectId: $cost->id,
        );

        return response()->json([
            'success' => true,
            'message' => 'Biaya proyek tersimpan dan otomatis masuk ke jurnal serta laba rugi.',
            'cost' => $cost->fresh(['creator', 'journalEntry', 'attachments']),
            'summary' => $this->costing->summary($project),
        ], 201);
    }

    private function validateProject(Request $request, ?Project $project = null): array
    {
        return $request->validate([
            'code' => [
                $project ? 'nullable' : 'nullable',
                'string',
                'max:80',
                Rule::unique('projects', 'code')->ignore($project?->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'client_name' => ['required', 'string', 'max:255'],
            'project_type' => ['required', 'string', 'in:design,contractor'],
            'status' => ['required', 'string', 'in:planned,active,on_hold,completed,cancelled'],
            'start_date' => ['nullable', 'date'],
            'target_end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'contract_value' => ['required', 'numeric', 'min:0', 'max:999999999999.99'],
            'budget_cost' => ['required', 'numeric', 'min:0', 'max:999999999999.99'],
            'progress' => ['required', 'numeric', 'between:0,100'],
            'manager_id' => ['nullable', 'integer', 'exists:users,id'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);
    }

    private function authorizeRead(Request $request)
    {
        $viewer = $request->user();
        abort_unless(
            $viewer->isCEO() || in_array($viewer->divisionKey(), ['operasional', 'finance'], true),
            403,
        );

        return $viewer;
    }

    private function authorizeWrite(Request $request)
    {
        $viewer = $this->authorizeRead($request);
        abort_unless($this->canWrite($viewer), 403);

        return $viewer;
    }

    private function canWrite($viewer): bool
    {
        return $viewer->isCEO() || in_array($viewer->role, ['mgr_ops', 'mgr_finance'], true);
    }

    private function nextProjectCode(string $type): string
    {
        do {
            $code = ($type === 'contractor' ? 'CTR-' : 'DSN-').now()->format('ym').'-'.Str::upper(Str::random(5));
        } while (Project::query()->where('code', $code)->exists());

        return $code;
    }

    private function auditProject(Request $request, $actor, Project $project, string $event): void
    {
        $this->audit->record(
            $event,
            actor: $actor,
            request: $request,
            metadata: [
                'code' => $project->code,
                'type' => $project->project_type,
                'status' => $project->status,
            ],
            subjectType: Project::class,
            subjectId: $project->id,
        );
    }
}
