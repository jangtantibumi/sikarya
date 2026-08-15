<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ApprovalRequest;
use App\Models\TeamRequest;
use App\Models\User;
use App\Services\ApprovalWorkflowService;
use App\Services\EmployeeIdentityService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TeamRequestController extends Controller
{
    public function __construct(
        private readonly ApprovalWorkflowService $workflowService,
        private readonly EmployeeIdentityService $identity,
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $query = TeamRequest::query()->where('status', 'pending');

        if (! $user->isCEO()) {
            $query->where('requester_username', $user->username);
        }

        return response()->json(
            $query->orderByDesc('created_at')->get()->map(function (TeamRequest $teamRequest): array {
                return [
                    'id' => $teamRequest->id,
                    'requester_username' => $teamRequest->requester_username,
                    'action' => $teamRequest->action,
                    'target_username' => $teamRequest->target_username,
                    'details' => collect($teamRequest->details ?? [])->except(['password'])->all(),
                    'status' => $teamRequest->status,
                    'approval_id' => $teamRequest->approvalRequest?->id,
                    'created_at' => $teamRequest->created_at?->toIso8601String(),
                ];
            })
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'action' => ['required', 'in:add,delete'],
            'target_username' => ['required_if:action,delete', 'nullable', 'string', 'exists:users,username'],
            'details' => ['required_if:action,add', 'nullable', 'array'],
            'details.name' => ['required_if:action,add', 'nullable', 'string', 'max:255'],
            'details.username' => ['nullable', 'string', 'max:100'],
            'details.email' => ['required_if:action,add', 'nullable', 'email', 'max:255'],
            'details.role' => ['required_if:action,add', 'nullable', 'string', 'max:100'],
            'details.job_title' => ['required_if:action,add', 'nullable', 'string', 'max:120'],
            'details.employment_type' => ['nullable', 'string', 'max:100'],
            'completion_status' => ['required_if:action,delete', 'nullable', 'string', 'in:completed,incomplete'],
            'convert_to_alumni' => ['sometimes', 'boolean'],
            'separation_reason' => ['required_if:action,delete', 'nullable', 'string', 'in:completed,terminated,resigned,other'],
            'separation_notes' => ['nullable', 'string', 'max:2000', 'required_if:separation_reason,other'],
            'effective_date' => ['required_if:action,delete', 'nullable', 'date', 'before_or_equal:today'],
        ]);

        $actor = $request->user();

        if (! $actor->isManager() && ! $actor->isCEO()) {
            abort(403);
        }

        if ($validated['action'] === 'add' && $actor->isManager()) {
            $expectedRole = match ($actor->divisionKey()) {
                'marketing' => 'staff_marketing',
                'operasional' => 'staff_ops',
                'finance' => 'staff_finance',
                'hrd' => 'staff_hrd',
                default => null,
            };

            if (! $expectedRole || ($validated['details']['role'] ?? null) !== $expectedRole) {
                throw ValidationException::withMessages([
                    'details.role' => 'Manager hanya dapat menambahkan staf pada divisi dan levelnya sendiri.',
                ]);
            }
        }

        if ($validated['action'] === 'delete') {
            $target = User::query()->where('username', $validated['target_username'])->firstOrFail();

            if ($target->isCEO()) {
                throw ValidationException::withMessages([
                    'target_username' => 'Akun CEO tidak dapat diajukan untuk dihapus.',
                ]);
            }

            if (! $actor->isCEO() && ! $actor->isManagerOf($target)) {
                throw ValidationException::withMessages([
                    'target_username' => 'Anda hanya dapat mengajukan penghapusan anggota tim sendiri.',
                ]);
            }
        }

        $details = collect($validated['details'] ?? [])->except(['password'])->all();
        if ($validated['action'] === 'add') {
            $details = [
                ...$details,
                ...$this->identity->preview(
                    $details['role'],
                    $details['employment_type'] ?? 'Full-Time',
                    $details['name'] ?? 'pegawai',
                ),
            ];
        } else {
            $details = collect($validated)->only([
                'completion_status',
                'convert_to_alumni',
                'separation_reason',
                'separation_notes',
                'effective_date',
            ])->all();
        }

        $teamRequest = TeamRequest::query()->create([
            'requester_username' => $actor->username,
            'action' => $validated['action'],
            'target_username' => $validated['target_username'] ?? null,
            'details' => $details !== [] ? $details : null,
            'status' => 'pending',
        ]);

        $initialManager = $actor->isStaff() ? $actor->manager() : null;
        $approval = $this->workflowService->createRequest(
            type: 'team_request',
            subject: $teamRequest,
            requester: $actor,
            division: $actor->divisionKey(),
            initialApprover: $initialManager,
            payload: [
                'action' => $teamRequest->action,
                'target_username' => $teamRequest->target_username,
                'separation' => $teamRequest->action === 'delete' ? $teamRequest->details : null,
            ],
        );

        return response()->json([
            'success' => true,
            'message' => $approval->status === 'pending_manager'
                ? 'Pengajuan berhasil dikirim kepada manager.'
                : 'Pengajuan berhasil dikirim kepada CEO.',
            'request' => [
                'id' => $teamRequest->id,
                'approval_id' => $approval->id,
                'status' => $approval->status,
            ],
        ], 201);
    }

    public function approve(Request $request, int $id)
    {
        $teamRequest = TeamRequest::query()->findOrFail($id);
        $approval = $this->approvalFor($teamRequest, $request->user());
        $updated = $this->workflowService->approve($approval, $request->user(), 'Disetujui');

        return response()->json([
            'success' => true,
            'message' => $updated->status === 'pending_ceo'
                ? 'Pengajuan diteruskan kepada CEO.'
                : 'Pengajuan disetujui dan data tim telah diperbarui.',
        ]);
    }

    public function reject(Request $request, int $id)
    {
        $validated = $request->validate([
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $teamRequest = TeamRequest::query()->findOrFail($id);
        $approval = $this->approvalFor($teamRequest, $request->user());
        $this->workflowService->reject(
            $approval,
            $request->user(),
            $validated['note'] ?? 'Pengajuan perubahan tim ditolak.',
        );

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan perubahan tim berhasil ditolak.',
        ]);
    }

    private function approvalFor(TeamRequest $teamRequest, User $actor): ApprovalRequest
    {
        $approval = $teamRequest->approvalRequest()
            ->whereIn('status', ['pending_manager', 'pending_ceo'])
            ->latest('id')
            ->first();

        if ($approval) {
            return $approval;
        }

        $requester = User::query()->where('username', $teamRequest->requester_username)->firstOrFail();

        return $this->workflowService->createRequest(
            type: 'team_request',
            subject: $teamRequest,
            requester: $requester,
            division: $requester->divisionKey(),
            initialApprover: $requester->isStaff() ? $requester->manager() : null,
            payload: [
                'action' => $teamRequest->action,
                'target_username' => $teamRequest->target_username,
            ],
        );
    }
}
