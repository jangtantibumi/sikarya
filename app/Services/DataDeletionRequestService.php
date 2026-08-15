<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\ChatMessage;
use App\Models\ClientInflow;
use App\Models\DataDeletionRequest;
use App\Models\ErpDocument;
use App\Models\Goal;
use App\Models\JournalEntry;
use App\Models\Kpi;
use App\Models\KpiPlan;
use App\Models\Lead;
use App\Models\LeaveRequest;
use App\Models\Project;
use App\Models\ProjectCost;
use App\Models\ResignationRequest;
use App\Models\Rule;
use App\Models\TalentReview;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DataDeletionRequestService
{
    private const RESOURCE_MODELS = [
        'task' => Task::class,
        'goal' => Goal::class,
        'kpi_plan' => KpiPlan::class,
        'kpi' => Kpi::class,
        'rule' => Rule::class,
        'lead' => Lead::class,
        'client_inflow' => ClientInflow::class,
        'leave_request' => LeaveRequest::class,
        'resignation_request' => ResignationRequest::class,
        'attendance' => Attendance::class,
        'chat_message' => ChatMessage::class,
        'talent_review' => TalentReview::class,
        'erp_document' => ErpDocument::class,
        'project' => Project::class,
        'project_cost' => ProjectCost::class,
        'journal_entry' => JournalEntry::class,
    ];

    private const HIGH_RISK_TYPES = [
        'client_inflow',
        'leave_request',
        'resignation_request',
        'attendance',
        'talent_review',
        'erp_document',
        'project',
        'project_cost',
        'journal_entry',
    ];

    public function __construct(
        private readonly ApprovalWorkflowService $workflow,
        private readonly DataDeletionExecutor $executor,
        private readonly SecurityAuditService $audit,
    ) {
    }

    public function supportedTypes(): array
    {
        return array_keys(self::RESOURCE_MODELS);
    }

    public function request(User $actor, string $resourceType, int $targetId, string $reason): array
    {
        $target = $this->findTarget($resourceType, $targetId);
        $this->authorizeRequest($actor, $resourceType, $target);

        $existing = DataDeletionRequest::query()
            ->where('resource_type', $resourceType)
            ->where('target_id', $targetId)
            ->whereIn('status', ['pending_manager', 'pending_ceo'])
            ->first();

        if ($existing) {
            throw ValidationException::withMessages([
                'deletion' => 'Data ini sudah memiliki pengajuan penghapusan yang masih diproses.',
            ]);
        }

        $isDirect = $actor->isCEO() || $this->isPrivateDraft($actor, $resourceType, $target);
        $highRisk = in_array($resourceType, self::HIGH_RISK_TYPES, true);
        $manager = $actor->isStaff() ? $actor->manager() : null;
        $initialStatus = $isDirect
            ? 'approved'
            : ($manager ? 'pending_manager' : 'pending_ceo');

        $deletion = DB::transaction(function () use (
            $actor,
            $resourceType,
            $target,
            $targetId,
            $reason,
            $initialStatus,
        ): DataDeletionRequest {
            return DataDeletionRequest::query()->create([
                'resource_type' => $resourceType,
                'target_type' => $target::class,
                'target_id' => $targetId,
                'target_label' => $this->label($resourceType, $target),
                'deletion_mode' => $this->deletionMode($resourceType, $target),
                'scope' => $this->isPrivateDraft($actor, $resourceType, $target) ? 'private_draft' : 'shared',
                'division' => $this->division($resourceType, $target, $actor),
                'requested_by_id' => $actor->id,
                'reason' => $reason,
                'status' => $initialStatus,
                'snapshot' => $this->snapshot($target),
            ]);
        });

        if ($isDirect) {
            $this->executor->execute($deletion, $actor);

            return [
                'deletion' => $deletion->fresh(['requester', 'executor']),
                'approval' => null,
                'message' => $actor->isCEO()
                    ? 'Data diproses sesuai kebijakan retensi dan tercatat pada audit.'
                    : 'Draft pribadi berhasil dihapus dan masih dapat dipulihkan oleh administrator.',
            ];
        }

        $approval = $this->workflow->createRequest(
            type: 'data_deletion',
            subject: $deletion,
            requester: $actor,
            division: $deletion->division,
            initialApprover: $manager,
            payload: [
                'resource_type' => $resourceType,
                'target_label' => $deletion->target_label,
                'deletion_mode' => $deletion->deletion_mode,
                'reason' => $reason,
                'requires_ceo_after_manager' => $highRisk && (bool) $manager,
            ],
        );

        $this->audit->record(
            'data.deletion_requested',
            actor: $actor,
            metadata: [
                'resource_type' => $resourceType,
                'target_label' => $deletion->target_label,
                'reason' => $reason,
                'approval_id' => $approval->id,
            ],
            subjectType: DataDeletionRequest::class,
            subjectId: $deletion->id,
        );

        return [
            'deletion' => $deletion->fresh(['requester']),
            'approval' => $approval,
            'message' => $approval->status === 'pending_manager'
                ? 'Pengajuan penghapusan telah dikirim kepada manager.'
                : 'Pengajuan penghapusan telah dikirim kepada CEO.',
        ];
    }

    private function findTarget(string $resourceType, int $targetId): Model
    {
        $class = self::RESOURCE_MODELS[$resourceType] ?? null;
        if (!$class) {
            throw ValidationException::withMessages(['resource_type' => 'Jenis data tidak didukung.']);
        }

        $query = $class::query();
        if (in_array(SoftDeletes::class, class_uses_recursive($class), true)) {
            $query->withTrashed();
        }

        $target = $query->findOrFail($targetId);
        if (method_exists($target, 'trashed') && $target->trashed()) {
            throw ValidationException::withMessages(['deletion' => 'Data tersebut sudah dihapus.']);
        }

        return $target;
    }

    private function authorizeRequest(User $actor, string $resourceType, Model $target): void
    {
        if ($actor->isCEO()) {
            return;
        }

        if ($resourceType === 'journal_entry') {
            abort_unless($actor->role === 'mgr_finance', 403);
            return;
        }

        if ($resourceType === 'client_inflow' || $resourceType === 'project_cost') {
            abort_unless($actor->divisionKey() === 'finance' || $actor->role === 'mgr_ops', 403);
            return;
        }

        if ($resourceType === 'attendance' && $actor->isHRD()) {
            return;
        }

        $ownerIds = $this->ownerIds($resourceType, $target);
        if (in_array($actor->id, $ownerIds, true)) {
            return;
        }

        if ($actor->isManager()) {
            $teamIds = User::query()
                ->where('parent', $actor->username)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

            if (array_intersect($ownerIds, $teamIds) !== []) {
                return;
            }

            $division = $this->division($resourceType, $target, $actor);
            if ($division && $division === $actor->divisionKey()) {
                return;
            }
        }

        abort(403, 'Anda tidak berwenang mengajukan penghapusan data ini.');
    }

    private function ownerIds(string $resourceType, Model $target): array
    {
        $ids = match ($resourceType) {
            'task' => [$target->user_id, $target->created_by_id],
            'goal' => [$target->created_by],
            'kpi_plan' => [$target->manager_id],
            'kpi' => [$target->plan?->manager_id],
            'rule' => [$target->created_by],
            'lead' => [$target->assigned_to],
            'client_inflow' => [User::query()->where('username', $target->created_by)->value('id')],
            'leave_request', 'resignation_request', 'attendance' => [$target->user_id],
            'chat_message' => [$target->sender_id],
            'talent_review' => [$target->user_id, $target->reviewer_id],
            'erp_document' => [$target->owner_user_id, $target->created_by_id],
            'project' => [$target->manager_id],
            'project_cost', 'journal_entry' => [$target->created_by_id],
            default => [],
        };

        return collect($ids)->filter()->map(fn ($id) => (int) $id)->unique()->values()->all();
    }

    private function isPrivateDraft(User $actor, string $resourceType, Model $target): bool
    {
        return $resourceType === 'task'
            && (int) $target->user_id === (int) $actor->id
            && (int) ($target->created_by_id ?? $target->user_id) === (int) $actor->id
            && in_array($target->status, ['draft', 'rejected', 'cancelled'], true);
    }

    private function deletionMode(string $resourceType, Model $target): string
    {
        return match ($resourceType) {
            'chat_message' => 'redact',
            'journal_entry' => 'reverse',
            'client_inflow', 'project_cost' => 'reverse_and_delete',
            'erp_document' => $target->status === 'draft' ? 'soft_delete' : 'revoke',
            default => 'soft_delete',
        };
    }

    private function division(string $resourceType, Model $target, User $actor): ?string
    {
        return match ($resourceType) {
            'goal' => $target->division,
            'kpi_plan' => $target->divisionKey(),
            'kpi' => $target->plan?->divisionKey(),
            'rule' => $target->division,
            'task' => $target->user?->divisionKey(),
            'lead' => $target->assignee?->divisionKey() ?? 'marketing',
            'client_inflow', 'journal_entry' => 'finance',
            'leave_request', 'resignation_request', 'attendance' => $target->user?->divisionKey(),
            'chat_message' => $actor->divisionKey(),
            'talent_review' => $target->user?->divisionKey(),
            'erp_document' => $target->owner?->divisionKey() ?? 'hrd',
            'project' => $target->manager?->divisionKey() ?? 'operasional',
            'project_cost' => $target->project?->manager?->divisionKey() ?? 'operasional',
            default => $actor->divisionKey(),
        };
    }

    private function label(string $resourceType, Model $target): string
    {
        return match ($resourceType) {
            'task' => 'Task: '.$target->title,
            'goal' => 'Goal: '.$target->title,
            'kpi_plan' => 'Rencana KPI: '.($target->goal?->title ?? '#'.$target->id),
            'kpi' => 'KPI: '.$target->title,
            'rule' => 'Aturan KPI: '.$target->condition,
            'lead' => 'Lead: '.$target->client_name,
            'client_inflow' => 'Pemasukan: '.$target->client_name.' / '.$target->date,
            'leave_request' => 'Cuti: '.($target->user?->name ?? '#'.$target->id),
            'resignation_request' => 'Resign: '.($target->user?->name ?? '#'.$target->id),
            'attendance' => 'Presensi: '.($target->user?->name ?? '#'.$target->id).' / '.$target->clock_in?->format('Y-m-d'),
            'chat_message' => 'Pesan: '.str($target->message)->squish()->limit(60),
            'talent_review' => 'Review Talent: '.($target->user?->name ?? '#'.$target->id),
            'erp_document' => 'Dokumen: '.$target->document_number,
            'project' => 'Proyek: '.$target->code.' - '.$target->name,
            'project_cost' => 'Biaya Proyek: '.$target->description,
            'journal_entry' => 'Jurnal: '.$target->reference,
            default => 'Data #'.$target->id,
        };
    }

    private function snapshot(Model $target): array
    {
        return Arr::except($target->getAttributes(), [
            'password',
            'otp_code',
            'gemini_api_key',
            'remember_token',
            'content',
            'message',
            'attachment_path',
        ]);
    }
}
