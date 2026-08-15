<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApprovalRequest;
use App\Models\DataDeletionRequest;
use App\Models\KpiPlan;
use App\Models\LeaveRequest;
use App\Models\ResignationRequest;
use App\Models\Task;
use App\Models\TeamRequest;
use App\Services\ApprovalWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ApprovalController extends Controller
{
    public function __construct(
        private readonly ApprovalWorkflowService $workflowService,
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();

        $query = ApprovalRequest::query()
            ->with(['requester', 'currentApprover', 'subject', 'steps.approver']);

        if ($request->boolean('history')) {
            $query->whereIn('status', ['approved', 'rejected', 'cancelled']);

            if ($user->isCEO()) {
                // CEO has company-wide audit visibility.
            } elseif ($user->isManager()) {
                $query->where(function ($builder) use ($user): void {
                    $builder
                        ->where('requester_id', $user->id)
                        ->orWhereHas('requester', function ($requesters) use ($user): void {
                            $requesters->where('parent', $user->username);
                        })
                        ->orWhereHas('steps', fn ($steps) => $steps->where('approver_id', $user->id));
                });
            } else {
                $query->where('requester_id', $user->id);
            }
        } elseif ($user->isCEO()) {
            $query->where('status', 'pending_ceo');
        } elseif ($user->isManager()) {
            $query
                ->where('status', 'pending_manager')
                ->where('current_approver_id', $user->id);
        } else {
            $query->whereRaw('1 = 0');
        }

        if ($request->filled('division') && $request->string('division')->toString() !== 'all') {
            $query->where('division', $request->string('division')->toString());
        }

        return response()->json(
            $query
                ->orderByDesc('submitted_at')
                ->orderByDesc('id')
                ->get()
                ->map(fn (ApprovalRequest $approval) => $this->formatApproval($approval, $user))
        );
    }

    public function approve(Request $request, ApprovalRequest $approvalRequest)
    {
        Gate::authorize('update', $approvalRequest);

        $validated = $request->validate([
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $updatedRequest = $this->workflowService->approve(
            $approvalRequest,
            $request->user(),
            $validated['note'] ?? null,
        );

        return response()->json([
            'success' => true,
            'message' => $updatedRequest->status === 'pending_ceo'
                ? 'Pengajuan disetujui dan diteruskan ke CEO.'
                : 'Pengajuan berhasil disetujui.',
            'approval' => $this->formatApproval($updatedRequest, $request->user()),
        ]);
    }

    public function reject(Request $request, ApprovalRequest $approvalRequest)
    {
        Gate::authorize('update', $approvalRequest);

        $validated = $request->validate([
            'note' => ['required', 'string', 'max:1000'],
        ]);

        $updatedRequest = $this->workflowService->reject(
            $approvalRequest,
            $request->user(),
            $validated['note'],
        );

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan berhasil ditolak.',
            'approval' => $this->formatApproval($updatedRequest, $request->user()),
        ]);
    }

    private function formatApproval(ApprovalRequest $approval, $viewer): array
    {
        return [
            'id' => $approval->id,
            'request_type' => $approval->request_type,
            'type_label' => match ($approval->request_type) {
                'leave' => 'Pengajuan Cuti',
                'team_request' => 'Perubahan Anggota Tim',
                'kpi_plan' => 'Rencana KPI',
                'task' => 'Pengajuan Task',
                'resignation' => 'Pengajuan Resign',
                'data_deletion' => 'Penghapusan Data',
                default => 'Pengajuan',
            },
            'division' => $approval->division,
            'status' => $approval->status,
            'submitted_at' => $approval->submitted_at?->toIso8601String(),
            'completed_at' => $approval->completed_at?->toIso8601String(),
            'requester' => $approval->requester ? [
                'id' => $approval->requester->id,
                'name' => $approval->requester->name,
                'username' => $approval->requester->username,
                'role' => $approval->requester->role,
            ] : null,
            'current_approver' => $approval->currentApprover ? [
                'id' => $approval->currentApprover->id,
                'name' => $approval->currentApprover->name,
            ] : null,
            'details' => $this->subjectDetails($approval->subject),
            'payload' => collect($approval->payload ?? [])->except(['password'])->all(),
            'decisions' => $approval->steps
                ->sortBy('sequence')
                ->map(fn ($step): array => [
                    'sequence' => $step->sequence,
                    'stage' => $step->approver_role === 'ceo' ? 'CEO' : 'Manager',
                    'status' => $step->status,
                    'note' => $step->decision_note,
                    'decided_at' => $step->decided_at?->toIso8601String(),
                    'approver' => $step->approver ? [
                        'name' => $step->approver->name,
                        'username' => $step->approver->username,
                    ] : null,
                ])
                ->values(),
            'actionable' => Gate::forUser($viewer)->allows('update', $approval),
        ];
    }

    private function subjectDetails($subject): array
    {
        return match (true) {
            $subject instanceof LeaveRequest => [
                'type' => $subject->type,
                'start_date' => $subject->start_date?->format('Y-m-d'),
                'end_date' => $subject->end_date?->format('Y-m-d'),
                'reason' => $subject->reason,
            ],
            $subject instanceof TeamRequest => [
                'action' => $subject->action,
                'target_username' => $subject->target_username,
                'new_staff' => collect($subject->details ?? [])->except(['password'])->all(),
                'separation' => $subject->action === 'delete'
                    ? collect($subject->details ?? [])->only([
                        'completion_status',
                        'separation_reason',
                        'separation_notes',
                        'effective_date',
                    ])->all()
                    : null,
            ],
            $subject instanceof KpiPlan => [
                'goal' => $subject->displayTitle(),
                'proposal_source' => $subject->goal_id ? 'ceo_goal' : 'manager_initiative',
                'kpis' => $subject->kpis->map(fn ($kpi) => [
                    'title' => $kpi->title,
                    'target_value' => $kpi->target_value,
                    'unit' => $kpi->unit,
                    'weight' => $kpi->weight,
                ])->values(),
            ],
            $subject instanceof Task => [
                'title' => $subject->title,
                'deadline' => $subject->deadline?->toIso8601String(),
                'kpi' => $subject->kpi?->title ?? $subject->relation,
            ],
            $subject instanceof ResignationRequest => [
                'last_working_date' => $subject->last_working_date?->format('Y-m-d'),
                'reason' => $subject->reason,
                'handover_notes' => $subject->handover_notes,
            ],
            $subject instanceof DataDeletionRequest => [
                'resource_type' => $subject->resource_type,
                'target_label' => $subject->target_label,
                'deletion_mode' => $subject->deletion_mode,
                'scope' => $subject->scope,
                'reason' => $subject->reason,
            ],
            default => [],
        };
    }
}
