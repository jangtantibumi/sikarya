<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ApprovalRequest;
use App\Models\ApprovalStep;
use App\Models\DataDeletionRequest;
use App\Models\KpiPlan;
use App\Models\TeamRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ApprovalWorkflowService
{
    public function __construct(
        private readonly WorkflowNotificationService $notifications,
        private readonly MetricAggregationService $metrics,
        private readonly DataDeletionExecutor $deletionExecutor,
    ) {}

    public function createRequest(
        string $type,
        Model $subject,
        User $requester,
        ?string $division = null,
        ?User $initialApprover = null,
        array $payload = [],
    ): ApprovalRequest {
        $managerApprover = $initialApprover?->isManager() ? $initialApprover : null;

        $approvalRequest = DB::transaction(function () use (
            $type,
            $subject,
            $requester,
            $division,
            $managerApprover,
            $payload,
        ): ApprovalRequest {
            $existing = ApprovalRequest::query()
                ->where('subject_type', $subject->getMorphClass())
                ->where('subject_id', $subject->getKey())
                ->whereIn('status', ['pending_manager', 'pending_ceo'])
                ->first();

            if ($existing) {
                return $existing;
            }

            return ApprovalRequest::query()->create([
                'request_type' => $type,
                'division' => $division ?? $requester->divisionKey(),
                'requester_id' => $requester->id,
                'subject_type' => $subject->getMorphClass(),
                'subject_id' => $subject->getKey(),
                'current_approver_id' => $managerApprover?->id,
                'current_step' => 1,
                'status' => $managerApprover ? 'pending_manager' : 'pending_ceo',
                'payload' => $payload,
                'submitted_at' => now(),
            ]);
        });

        $this->notifyCreated($approvalRequest->fresh(['requester', 'currentApprover']));

        return $approvalRequest;
    }

    public function approve(ApprovalRequest $approvalRequest, User $approver, ?string $note = null): ApprovalRequest
    {
        $previousStatus = $approvalRequest->status;

        $updated = DB::transaction(function () use ($approvalRequest, $approver, $note): ApprovalRequest {
            /** @var ApprovalRequest $request */
            $request = ApprovalRequest::query()
                ->with('subject')
                ->lockForUpdate()
                ->findOrFail($approvalRequest->id);

            $this->assertCanDecide($request, $approver);

            ApprovalStep::query()->create([
                'approval_request_id' => $request->id,
                'sequence' => $request->current_step,
                'approver_id' => $approver->id,
                'approver_role' => $approver->role,
                'status' => 'approved',
                'decision_note' => $note,
                'decided_at' => now(),
            ]);

            if ($request->status === 'pending_manager' && $this->requiresCeoAfterManager($request)) {
                $request->forceFill([
                    'status' => 'pending_ceo',
                    'current_step' => $request->current_step + 1,
                    'current_approver_id' => null,
                ])->save();

                $this->applyPendingCeoToSubject($request);
            } else {
                $request->forceFill([
                    'status' => 'approved',
                    'current_approver_id' => null,
                    'completed_at' => now(),
                ])->save();

                $this->applyApprovalToSubject($request, $approver);
            }

            return $request->fresh(['requester', 'currentApprover', 'subject', 'steps.approver']);
        });

        $this->notifyApproved($updated, $approver, $previousStatus);

        return $updated;
    }

    public function reject(ApprovalRequest $approvalRequest, User $approver, string $note): ApprovalRequest
    {
        $updated = DB::transaction(function () use ($approvalRequest, $approver, $note): ApprovalRequest {
            /** @var ApprovalRequest $request */
            $request = ApprovalRequest::query()
                ->with('subject')
                ->lockForUpdate()
                ->findOrFail($approvalRequest->id);

            $this->assertCanDecide($request, $approver);

            ApprovalStep::query()->create([
                'approval_request_id' => $request->id,
                'sequence' => $request->current_step,
                'approver_id' => $approver->id,
                'approver_role' => $approver->role,
                'status' => 'rejected',
                'decision_note' => $note,
                'decided_at' => now(),
            ]);

            $request->forceFill([
                'status' => 'rejected',
                'current_approver_id' => null,
                'completed_at' => now(),
            ])->save();

            $this->applyRejectionToSubject($request);

            return $request->fresh(['requester', 'currentApprover', 'subject', 'steps.approver']);
        });

        $this->notifyRejected($updated, $approver, $note);

        return $updated;
    }

    private function assertCanDecide(ApprovalRequest $request, User $approver): void
    {
        if (! in_array($request->status, ['pending_manager', 'pending_ceo'], true)) {
            throw ValidationException::withMessages([
                'approval' => 'Pengajuan ini sudah diproses sebelumnya.',
            ]);
        }

        $isManagerDecision = $request->status === 'pending_manager'
            && $request->current_approver_id === $approver->id
            && $approver->isManager();

        $isCeoDecision = $request->status === 'pending_ceo' && $approver->isCEO();

        if (! $isManagerDecision && ! $isCeoDecision) {
            throw ValidationException::withMessages([
                'approval' => 'Anda bukan pihak yang berwenang memproses tahap pengajuan ini.',
            ]);
        }
    }

    private function requiresCeoAfterManager(ApprovalRequest $request): bool
    {
        if ($request->request_type === 'data_deletion') {
            return (bool) data_get($request->payload, 'requires_ceo_after_manager', false);
        }

        return in_array($request->request_type, ['leave', 'team_request', 'resignation'], true);
    }

    private function applyPendingCeoToSubject(ApprovalRequest $request): void
    {
        $subject = $request->subject;

        if (! $subject) {
            return;
        }

        if (method_exists($subject, 'markAsPendingCeo')) {
            $subject->markAsPendingCeo();

            return;
        }

        if (in_array('status', $subject->getFillable(), true)) {
            $subject->forceFill(['status' => 'pending_ceo'])->save();
        }
    }

    private function applyApprovalToSubject(ApprovalRequest $request, User $approver): void
    {
        $subject = $request->subject;

        if (! $subject) {
            return;
        }

        if ($subject instanceof DataDeletionRequest) {
            $this->deletionExecutor->execute($subject, $approver);

            return;
        }

        if (method_exists($subject, 'markAsApproved')) {
            if ($subject instanceof TeamRequest) {
                $subject->markAsApproved($approver);

                return;
            }

            if ($subject instanceof KpiPlan && $subject->goal_id !== null) {
                KpiPlan::query()
                    ->where('goal_id', $subject->goal_id)
                    ->where('manager_id', $subject->manager_id)
                    ->where('status', 'approved')
                    ->where('id', '!=', $subject->getKey())
                    ->update(['status' => 'superseded']);
            }

            $subject->markAsApproved();

            if ($subject instanceof KpiPlan) {
                $this->metrics->recalculateKpiPlan($subject->fresh(['kpis', 'goal']));
            }

            return;
        }

        if (in_array('status', $subject->getFillable(), true)) {
            $subject->forceFill(['status' => 'approved'])->save();
        }
    }

    private function applyRejectionToSubject(ApprovalRequest $request): void
    {
        $subject = $request->subject;

        if (! $subject) {
            return;
        }

        if (method_exists($subject, 'markAsRejected')) {
            $subject->markAsRejected();

            return;
        }

        if (in_array('status', $subject->getFillable(), true)) {
            $subject->forceFill(['status' => 'rejected'])->save();
        }
    }

    private function notifyCreated(ApprovalRequest $request): void
    {
        $label = $this->requestLabel($request);
        $keyPrefix = "approval:{$request->id}:created";

        $this->notifications->send(
            $request->requester,
            'Pengajuan berhasil dikirim',
            "{$label} telah tercatat dan menunggu persetujuan.",
            "{$keyPrefix}:requester",
            'approval',
            '/#approval',
            ['approval_id' => $request->id, 'status' => $request->status],
        );

        $approvers = $request->status === 'pending_manager'
            ? collect([$request->currentApprover])
            : $this->notifications->ceos();

        $this->notifications->send(
            $approvers,
            'Pengajuan baru menunggu persetujuan',
            "{$label} dari {$request->requester->name} memerlukan keputusan Anda.",
            "{$keyPrefix}:approver",
            'approval',
            '/#approval',
            ['approval_id' => $request->id, 'status' => $request->status],
        );

        if (in_array($request->request_type, ['leave', 'resignation'], true)) {
            $this->notifications->send(
                $this->notifications->hrdUsers(),
                $request->request_type === 'leave' ? 'Informasi pengajuan cuti' : 'Tembusan pengajuan resign',
                $request->request_type === 'leave'
                    ? "{$request->requester->name} mengajukan cuti. HRD akan menerima pembaruan pada setiap tahap."
                    : "{$request->requester->name} mengajukan resign. HRD menerima tembusan dan pembaruan pada setiap tahap.",
                "{$keyPrefix}:hrd",
                $request->request_type,
                '/#hrd',
                ['approval_id' => $request->id, 'status' => $request->status],
            );
        }
    }

    private function notifyApproved(ApprovalRequest $request, User $approver, string $previousStatus): void
    {
        $label = $this->requestLabel($request);

        if ($request->status === 'pending_ceo') {
            $this->notifications->send(
                $request->requester,
                'Pengajuan diteruskan ke CEO',
                "{$label} telah disetujui {$approver->name} dan kini menunggu keputusan CEO.",
                "approval:{$request->id}:pending_ceo:requester",
                'approval',
                '/#approval',
                ['approval_id' => $request->id, 'status' => 'pending_ceo'],
            );

            $this->notifications->send(
                $this->notifications->ceos(),
                'Pengajuan menunggu keputusan CEO',
                "{$label} dari {$request->requester->name} telah lolos persetujuan manager.",
                "approval:{$request->id}:pending_ceo:ceo",
                'approval',
                '/#approval',
                ['approval_id' => $request->id, 'status' => 'pending_ceo'],
            );
        } else {
            $this->notifications->send(
                $request->requester,
                'Pengajuan disetujui',
                "{$label} telah disetujui oleh {$approver->name}.",
                "approval:{$request->id}:approved:requester",
                'approval',
                '/#approval',
                ['approval_id' => $request->id, 'status' => 'approved'],
            );

            if ($request->request_type === 'kpi_plan') {
                $this->notifications->send(
                    $this->notifications->usersForDivision($request->division),
                    'KPI divisi telah disahkan',
                    "{$label} telah disahkan CEO dan sekarang dapat digunakan untuk mengaitkan task.",
                    "approval:{$request->id}:approved:division",
                    'kpi',
                    '/#kpi-tasks',
                    ['approval_id' => $request->id, 'status' => 'approved'],
                );
            }
        }

        if (in_array($request->request_type, ['leave', 'resignation'], true)) {
            $this->notifications->send(
                $this->notifications->hrdUsers(),
                $request->request_type === 'leave' ? 'Pembaruan status cuti' : 'Pembaruan status resign',
                "{$label} berubah dari {$previousStatus} menjadi {$request->status}.",
                "approval:{$request->id}:{$request->status}:hrd",
                $request->request_type,
                '/#hrd',
                ['approval_id' => $request->id, 'status' => $request->status],
            );
        }
    }

    private function notifyRejected(ApprovalRequest $request, User $approver, string $note): void
    {
        $label = $this->requestLabel($request);

        $this->notifications->send(
            $request->requester,
            'Pengajuan ditolak',
            "{$label} ditolak oleh {$approver->name}. Alasan: {$note}",
            "approval:{$request->id}:rejected:requester",
            'approval',
            '/#approval',
            ['approval_id' => $request->id, 'status' => 'rejected'],
        );

        if (in_array($request->request_type, ['leave', 'resignation'], true)) {
            $this->notifications->send(
                $this->notifications->hrdUsers(),
                $request->request_type === 'leave' ? 'Pengajuan cuti ditolak' : 'Pengajuan resign ditolak',
                "{$label} ditolak oleh {$approver->name}.",
                "approval:{$request->id}:rejected:hrd",
                $request->request_type,
                '/#hrd',
                ['approval_id' => $request->id, 'status' => 'rejected'],
            );
        }
    }

    private function requestLabel(ApprovalRequest $request): string
    {
        return match ($request->request_type) {
            'leave' => 'Pengajuan cuti',
            'team_request' => 'Pengajuan perubahan anggota tim',
            'kpi_plan' => 'Rencana KPI',
            'task' => 'Pengajuan task',
            'resignation' => 'Pengajuan resign',
            'data_deletion' => 'Pengajuan penghapusan data',
            default => 'Pengajuan',
        };
    }
}
