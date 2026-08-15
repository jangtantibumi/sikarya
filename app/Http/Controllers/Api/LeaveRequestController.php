<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApprovalRequest;
use App\Models\LeaveRequest;
use App\Services\ApprovalWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class LeaveRequestController extends Controller
{
    public function __construct(
        private readonly ApprovalWorkflowService $workflowService,
    ) {}

    public function index(Request $request)
    {
        Gate::authorize('viewAny', LeaveRequest::class);

        $user = $request->user();
        $query = LeaveRequest::query()->with('user');

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

        $leaves = $query
            ->orderByDesc('created_at')
            ->get();

        $approvalMap = ApprovalRequest::query()
            ->where('subject_type', (new LeaveRequest)->getMorphClass())
            ->whereIn('subject_id', $leaves->pluck('id'))
            ->latest('id')
            ->get()
            ->keyBy('subject_id');

        return response()->json($leaves->map(function (LeaveRequest $leave) use ($approvalMap): array {
            $approval = $approvalMap->get($leave->id);

            return [
                'id' => $leave->id,
                'username' => $leave->user?->username,
                'name' => $leave->user?->name,
                'division' => $leave->user?->divisionKey(),
                'type' => $leave->type,
                'startDate' => $leave->start_date?->format('Y-m-d'),
                'endDate' => $leave->end_date?->format('Y-m-d'),
                'reason' => $leave->reason,
                'status' => $leave->status,
                'approver' => $approval?->currentApprover?->username,
                'approval_id' => $approval?->id,
                'can_edit' => $leave->user_id === request()->user()->id
                    && in_array($leave->status, ['pending_manager', 'pending_ceo'], true),
            ];
        }));
    }

    public function store(Request $request)
    {
        Gate::authorize('create', LeaveRequest::class);

        $validated = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'max:2000'],
            'type' => ['required', 'string', 'max:100'],
        ]);

        $user = $request->user();

        $hasOverlap = LeaveRequest::query()
            ->where('user_id', $user->id)
            ->whereIn('status', ['pending_manager', 'pending_ceo', 'approved'])
            ->whereDate('start_date', '<=', $validated['end_date'])
            ->whereDate('end_date', '>=', $validated['start_date'])
            ->exists();

        if ($hasOverlap) {
            throw ValidationException::withMessages([
                'start_date' => 'Sudah ada pengajuan cuti aktif pada rentang tanggal tersebut.',
            ]);
        }

        $initialManager = $user->isStaff() ? $user->manager() : null;

        if ($user->isStaff() && ! $initialManager) {
            throw ValidationException::withMessages([
                'approval' => 'Atasan langsung belum terdaftar. Hubungi HRD sebelum mengajukan cuti.',
            ]);
        }

        $leave = LeaveRequest::query()->create([
            'user_id' => $user->id,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'reason' => $validated['reason'],
            'type' => $validated['type'],
            'status' => $initialManager ? 'pending_manager' : 'pending_ceo',
        ]);

        $approval = $this->workflowService->createRequest(
            type: 'leave',
            subject: $leave,
            requester: $user,
            division: $user->divisionKey(),
            initialApprover: $initialManager,
            payload: [
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'type' => $validated['type'],
            ],
        );

        return response()->json([
            'success' => true,
            'message' => $approval->status === 'pending_manager'
                ? 'Pengajuan cuti berhasil dikirim kepada manager.'
                : 'Pengajuan cuti berhasil dikirim kepada CEO.',
            'leave' => $leave->fresh('user'),
            'approval_id' => $approval->id,
            'status' => $approval->status,
        ], 201);
    }

    public function update(Request $request, LeaveRequest $leaveRequest)
    {
        $actor = $request->user();
        abort_unless(
            $leaveRequest->user_id === $actor->id
                && in_array($leaveRequest->status, ['pending_manager', 'pending_ceo'], true),
            403,
            'Pengajuan cuti hanya dapat diedit oleh pembuat selama masih menunggu keputusan.',
        );

        $validated = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'max:2000'],
            'type' => ['required', 'string', 'max:100'],
        ]);

        $overlap = LeaveRequest::query()
            ->where('user_id', $actor->id)
            ->where('id', '!=', $leaveRequest->id)
            ->whereIn('status', ['pending_manager', 'pending_ceo', 'approved'])
            ->whereDate('start_date', '<=', $validated['end_date'])
            ->whereDate('end_date', '>=', $validated['start_date'])
            ->exists();
        if ($overlap) {
            throw ValidationException::withMessages([
                'start_date' => 'Sudah ada pengajuan cuti aktif pada rentang tanggal tersebut.',
            ]);
        }

        $leaveRequest->forceFill($validated)->save();
        $approval = ApprovalRequest::query()
            ->where('subject_type', $leaveRequest->getMorphClass())
            ->where('subject_id', $leaveRequest->id)
            ->whereIn('status', ['pending_manager', 'pending_ceo'])
            ->latest('id')
            ->first();
        if ($approval) {
            $approval->forceFill([
                'payload' => [
                    'start_date' => $validated['start_date'],
                    'end_date' => $validated['end_date'],
                    'type' => $validated['type'],
                    'edited_at' => now()->toIso8601String(),
                ],
            ])->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan cuti berhasil diperbarui tanpa mengubah antrean approval.',
            'leave' => $leaveRequest->fresh('user'),
        ]);
    }
}
