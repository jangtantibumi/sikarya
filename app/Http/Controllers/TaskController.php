<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ApprovalRequest;
use App\Models\Kpi;
use App\Models\Task;
use App\Models\User;
use App\Services\ApprovalWorkflowService;
use App\Services\DataDeletionRequestService;
use App\Services\MetricAggregationService;
use App\Services\RecordAttachmentService;
use App\Services\WorkflowNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class TaskController extends Controller
{
    public function __construct(
        private readonly MetricAggregationService $metricService,
        private readonly ApprovalWorkflowService $workflowService,
        private readonly WorkflowNotificationService $notifications,
        private readonly DataDeletionRequestService $deletions,
        private readonly RecordAttachmentService $attachments,
    ) {}

    public function index(Request $request)
    {
        Gate::authorize('viewAny', Task::class);

        $user = $request->user();
        $query = Task::query()->with(['user', 'kpi.plan.goal', 'creator', 'attachments']);

        if (! $user->isCEO() && ! $user->isHRD()) {
            if ($user->isManager()) {
                $query->where(function ($builder) use ($user): void {
                    $builder
                        ->where('user_id', $user->id)
                        ->orWhereHas('user', fn ($staff) => $staff->where('parent', $user->username));
                });
            } else {
                $query->where('user_id', $user->id);
            }
        }

        $tasks = $query->orderByDesc('created_at')->get();

        $approvalMap = ApprovalRequest::query()
            ->where('subject_type', (new Task)->getMorphClass())
            ->whereIn('subject_id', $tasks->pluck('id'))
            ->latest('id')
            ->get()
            ->keyBy('subject_id');

        return response()->json(
            $tasks->map(fn (Task $task) => $this->formatTask($task, $approvalMap->get($task->id)))
        );
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Task::class);

        $validated = $request->validate([
            'username' => ['nullable', 'string', 'exists:users,username'],
            'title' => ['required', 'string', 'max:255'],
            'deadline' => ['nullable', 'date'],
            'kpi_id' => ['nullable', 'integer', 'exists:kpis,id'],
            'relation' => ['nullable', 'string', 'max:255'],
            'metric_value' => ['nullable', 'numeric'],
            'attachment' => ['nullable', 'file', 'max:10240', 'mimes:pdf,doc,docx,xls,xlsx,csv,txt,jpg,jpeg,png,webp,zip'],
        ]);

        $actor = $request->user();
        $target = isset($validated['username'])
            ? User::query()->where('username', $validated['username'])->firstOrFail()
            : $actor;

        if ($target->id !== $actor->id && ! $actor->isCEO() && ! $actor->isManagerOf($target)) {
            abort(403, 'Anda hanya dapat memberikan task kepada anggota tim sendiri.');
        }

        $kpi = isset($validated['kpi_id']) ? Kpi::query()->with('plan.goal')->findOrFail($validated['kpi_id']) : null;

        if ($kpi) {
            if ($kpi->plan->status !== 'approved') {
                throw ValidationException::withMessages([
                    'kpi_id' => 'Task hanya dapat ditautkan ke KPI yang sudah disahkan CEO.',
                ]);
            }

            if ($kpi->plan->divisionKey() !== $target->divisionKey()) {
                throw ValidationException::withMessages([
                    'kpi_id' => 'KPI yang dipilih bukan milik divisi karyawan tujuan.',
                ]);
            }
        }

        $requiresApproval = $actor->id === $target->id && ! $actor->isCEO();
        $manager = $requiresApproval && $actor->isStaff() ? $actor->manager() : null;

        if ($requiresApproval && $actor->isStaff() && ! $manager) {
            throw ValidationException::withMessages([
                'approval' => 'Atasan langsung belum terdaftar untuk menyetujui task ini.',
            ]);
        }

        $task = DB::transaction(function () use (
            $validated,
            $actor,
            $target,
            $kpi,
            $requiresApproval,
            $manager,
        ): Task {
            return Task::query()->create([
                'user_id' => $target->id,
                'created_by_id' => $actor->id,
                'title' => $validated['title'],
                'status' => $requiresApproval ? ($manager ? 'pending_manager' : 'pending_ceo') : 'in_progress',
                'deadline' => $validated['deadline'] ?? null,
                'relation' => $kpi?->title ?? ($validated['relation'] ?? null),
                'kpi_id' => $kpi?->id,
                'metric_value' => $validated['metric_value'] ?? null,
                'approved_at' => $requiresApproval ? null : now(),
            ]);
        });

        $approval = null;
        if ($requiresApproval) {
            $approval = $this->workflowService->createRequest(
                type: 'task',
                subject: $task,
                requester: $actor,
                division: $actor->divisionKey(),
                initialApprover: $manager,
                payload: [
                    'title' => $task->title,
                    'deadline' => $task->deadline?->format('Y-m-d'),
                    'kpi_id' => $task->kpi_id,
                ],
            );
        } else {
            $this->notifications->send(
                $target,
                'Task baru diberikan',
                "{$actor->name} memberikan task \"{$task->title}\" kepada Anda.",
                "task:{$task->id}:assigned:{$target->id}",
                'task',
                '/#kpi-tasks',
                ['task_id' => $task->id, 'status' => $task->status],
            );
        }

        if ($request->hasFile('attachment')) {
            $this->attachments->store(
                $task,
                $request->file('attachment'),
                $actor,
                'task_brief',
            );
        }

        return response()->json([
            'success' => true,
            'message' => $requiresApproval
                ? ($manager ? 'Task berhasil diajukan kepada manager.' : 'Task berhasil diajukan kepada CEO.')
                : 'Task berhasil dibuat dan langsung aktif.',
            'task' => $this->formatTask(
                $task->fresh(['user', 'kpi.plan.goal', 'creator', 'attachments']),
                $approval,
            ),
        ], 201);
    }

    public function update(Request $request, int $id)
    {
        $task = Task::query()->with(['user', 'kpi.plan.goal', 'creator', 'attachments'])->findOrFail($id);
        $actor = $request->user();
        $isCreator = $task->created_by_id === $actor->id;
        $isOwnSubmission = $task->user_id === $actor->id && $isCreator;
        $editableStatus = in_array($task->status, [
            'pending_manager',
            'in_progress',
            'revision_requested',
            'rejected',
        ], true);

        abort_unless(($actor->isCEO() || $isCreator || $isOwnSubmission) && $editableStatus, 403,
            'Task hanya dapat diedit pembuatnya sebelum hasil dikirim atau diverifikasi.');

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'deadline' => ['nullable', 'date'],
            'kpi_id' => ['nullable', 'integer', 'exists:kpis,id'],
            'relation' => ['nullable', 'string', 'max:255'],
            'metric_value' => ['nullable', 'numeric'],
            'attachment' => ['nullable', 'file', 'max:10240', 'mimes:pdf,doc,docx,xls,xlsx,csv,txt,jpg,jpeg,png,webp,zip'],
        ]);

        $kpi = isset($validated['kpi_id'])
            ? Kpi::query()->with('plan.goal')->findOrFail($validated['kpi_id'])
            : null;
        if ($kpi && (
            $kpi->plan->status !== 'approved'
            || $kpi->plan->divisionKey() !== $task->user?->divisionKey()
        )) {
            throw ValidationException::withMessages([
                'kpi_id' => 'KPI yang dipilih belum disahkan atau bukan milik divisi penerima task.',
            ]);
        }

        $task->forceFill([
            'title' => $validated['title'],
            'deadline' => $validated['deadline'] ?? null,
            'kpi_id' => $kpi?->id,
            'relation' => $kpi?->title ?? ($validated['relation'] ?? 'Tugas Mandiri'),
            'metric_value' => $validated['metric_value'] ?? $task->metric_value,
        ])->save();

        if ($request->hasFile('attachment')) {
            $this->attachments->store($task, $request->file('attachment'), $actor, 'task_brief');
        }

        $approval = $task->approvalRequest()->latest('id')->first();
        if ($approval && in_array($approval->status, ['pending_manager', 'pending_ceo'], true)) {
            $approval->forceFill([
                'payload' => array_replace($approval->payload ?? [], [
                    'title' => $task->title,
                    'deadline' => $task->deadline?->format('Y-m-d'),
                    'kpi_id' => $task->kpi_id,
                    'edited_at' => now()->toIso8601String(),
                ]),
            ])->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Task dan lampirannya berhasil diperbarui.',
            'task' => $this->formatTask(
                $task->fresh(['user', 'kpi.plan.goal', 'creator', 'attachments']),
                $approval,
            ),
        ]);
    }

    public function updateStatus(Request $request, int $id)
    {
        $task = Task::query()->with(['user', 'kpi.plan.goal', 'creator', 'attachments'])->findOrFail($id);
        Gate::authorize('update', $task);

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:in_progress,submitted_for_review,verified,revision_requested,rejected,cancelled,done'],
            'evidence' => ['nullable', 'string', 'max:5000'],
            'feedback' => ['nullable', 'string', 'max:2000'],
            'metric_value' => ['nullable', 'numeric'],
            'evidence_attachment' => ['nullable', 'file', 'max:10240', 'mimes:pdf,doc,docx,xls,xlsx,csv,txt,jpg,jpeg,png,webp,zip'],
        ]);

        $actor = $request->user();
        $requestedStatus = $validated['status'] === 'done'
            ? 'submitted_for_review'
            : $validated['status'];

        $approval = $task->approvalRequest()->latest('id')->first();

        if ($task->status === 'pending_manager') {
            if (! $actor->isManager() || ! $actor->isManagerOf($task->user) || ! $approval) {
                abort(403, 'Task ini masih menunggu keputusan manager terkait.');
            }

            if ($requestedStatus === 'in_progress') {
                $this->workflowService->approve($approval, $actor, $validated['feedback'] ?? null);
            } elseif ($requestedStatus === 'rejected') {
                $this->workflowService->reject(
                    $approval,
                    $actor,
                    $validated['feedback'] ?? 'Task belum dapat disetujui.',
                );
            } else {
                throw ValidationException::withMessages([
                    'status' => 'Manager hanya dapat menyetujui atau menolak task yang diajukan.',
                ]);
            }

            $task->refresh();
        } elseif ($requestedStatus === 'submitted_for_review') {
            if ($actor->id !== $task->user_id || ! in_array($task->status, ['in_progress', 'revision_requested'], true)) {
                throw ValidationException::withMessages([
                    'status' => 'Hanya pemilik task aktif yang dapat mengirim hasil untuk ditinjau.',
                ]);
            }

            $task->forceFill([
                'status' => 'submitted_for_review',
                'evidence' => $validated['evidence'] ?? $task->evidence,
                'metric_value' => $validated['metric_value'] ?? $task->metric_value,
                'submitted_at' => now(),
            ])->save();

            if ($request->hasFile('evidence_attachment')) {
                $this->attachments->store(
                    $task,
                    $request->file('evidence_attachment'),
                    $actor,
                    'task_evidence',
                );
            }

            $reviewers = $task->user->isStaff()
                ? collect([$task->user->manager()])
                : $this->notifications->ceos();

            $this->notifications->send(
                $reviewers,
                'Hasil task menunggu verifikasi',
                "{$task->user->name} mengirim hasil task \"{$task->title}\" untuk ditinjau.",
                "task:{$task->id}:submitted:reviewer",
                'task',
                '/#kpi-tasks',
                ['task_id' => $task->id, 'status' => 'submitted_for_review'],
            );
        } elseif (in_array($requestedStatus, ['verified', 'revision_requested'], true)) {
            $canReview = $task->user->isStaff()
                ? $actor->isManagerOf($task->user)
                : $actor->isCEO();

            if (! $canReview || $task->status !== 'submitted_for_review') {
                throw ValidationException::withMessages([
                    'status' => 'Task hanya dapat diverifikasi oleh atasan setelah hasil dikirim.',
                ]);
            }

            $task->forceFill([
                'status' => $requestedStatus,
                'feedback' => $validated['feedback'] ?? $task->feedback,
                'verified_at' => $requestedStatus === 'verified' ? now() : null,
            ])->save();

            $this->notifications->send(
                $task->user,
                $requestedStatus === 'verified' ? 'Task berhasil diverifikasi' : 'Task memerlukan perbaikan',
                $requestedStatus === 'verified'
                    ? "Task \"{$task->title}\" telah diverifikasi oleh {$actor->name}."
                    : "Task \"{$task->title}\" dikembalikan oleh {$actor->name}. ".($validated['feedback'] ?? ''),
                "task:{$task->id}:{$requestedStatus}:".now()->format('YmdHis').':owner',
                'task',
                '/#kpi-tasks',
                ['task_id' => $task->id, 'status' => $requestedStatus],
            );
        } elseif ($requestedStatus === 'cancelled') {
            if ($actor->id !== $task->user_id && ! $actor->isCEO()) {
                abort(403);
            }

            $task->forceFill(['status' => 'cancelled'])->save();
        } else {
            throw ValidationException::withMessages([
                'status' => 'Perubahan status tersebut tidak diizinkan.',
            ]);
        }

        if ($task->kpi) {
            $this->metricService->recalculateKpi($task->kpi);
        }

        return response()->json([
            'success' => true,
            'message' => 'Status task berhasil diperbarui.',
            'task' => $this->formatTask($task->fresh(['user', 'kpi.plan.goal', 'creator', 'attachments']), $approval),
        ]);
    }

    public function destroy(Request $request, int $id)
    {
        $task = Task::query()->with('kpi')->findOrFail($id);
        Gate::authorize('delete', $task);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
        ]);
        $result = $this->deletions->request(
            $request->user(),
            'task',
            $task->id,
            $validated['reason'],
        );

        return response()->json([
            'success' => true,
            ...$result,
        ], $result['approval'] ? 202 : 200);
    }

    private function formatTask(Task $task, ?ApprovalRequest $approval = null): array
    {
        $viewer = request()->user();
        $canEdit = $viewer
            && ($viewer->isCEO() || $task->created_by_id === $viewer->id)
            && in_array($task->status, ['pending_manager', 'in_progress', 'revision_requested', 'rejected'], true);

        return [
            'id' => $task->id,
            'username' => $task->user?->username ?? 'unknown',
            'created_by' => $task->creator?->username,
            'title' => $task->title,
            'status' => $task->status,
            'deadline' => $task->deadline?->toIso8601String(),
            'relation' => $task->kpi?->title ?? $task->relation,
            'kpi_id' => $task->kpi_id,
            'goal_id' => $task->kpi?->plan?->goal_id,
            'evidence' => $task->evidence,
            'feedback' => $task->feedback,
            'metric_value' => $task->metric_value,
            'approval_id' => $approval?->id,
            'can_edit' => $canEdit,
            'edit_lock_reason' => $canEdit
                ? null
                : 'Data terkunci setelah hasil dikirim, diverifikasi, dibatalkan, atau jika Anda bukan pembuatnya.',
            'attachments' => $task->attachments->map(fn ($attachment): array => [
                'id' => $attachment->id,
                'name' => $attachment->original_name,
                'category' => $attachment->category,
                'size_bytes' => $attachment->size_bytes,
                'download_url' => route('record-attachments.download', $attachment),
            ])->values(),
        ];
    }
}
