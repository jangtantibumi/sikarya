<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApprovalRequest;
use App\Models\ResignationRequest;
use App\Services\ApprovalWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ResignationRequestController extends Controller
{
    public function __construct(
        private readonly ApprovalWorkflowService $workflowService,
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $requests = ResignationRequest::query()
            ->with('user:id,name,username,role,job_title')
            ->where('user_id', $user->id)
            ->latest('id')
            ->get();

        $approvalMap = ApprovalRequest::query()
            ->where('subject_type', (new ResignationRequest)->getMorphClass())
            ->whereIn('subject_id', $requests->pluck('id'))
            ->latest('id')
            ->get()
            ->keyBy('subject_id');

        return response()->json($requests->map(function (ResignationRequest $resignation) use ($approvalMap): array {
            $approval = $approvalMap->get($resignation->id);

            return [
                'id' => $resignation->id,
                'last_working_date' => $resignation->last_working_date?->format('Y-m-d'),
                'reason' => $resignation->reason,
                'handover_notes' => $resignation->handover_notes,
                'status' => $resignation->status,
                'approval_id' => $approval?->id,
                'submitted_at' => $resignation->created_at?->toIso8601String(),
                'can_edit' => in_array($resignation->status, ['pending_manager', 'pending_ceo'], true),
            ];
        }));
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if ($user->isCEO()) {
            throw ValidationException::withMessages([
                'approval' => 'Akun CEO tidak memiliki atasan dalam struktur organisasi.',
            ]);
        }

        $validated = $request->validate([
            'last_working_date' => ['required', 'date', 'after_or_equal:today'],
            'reason' => ['required', 'string', 'max:3000'],
            'handover_notes' => ['nullable', 'string', 'max:3000'],
        ]);

        if (ResignationRequest::query()
            ->where('user_id', $user->id)
            ->whereIn('status', ['pending_manager', 'pending_ceo'])
            ->exists()) {
            throw ValidationException::withMessages([
                'approval' => 'Masih ada pengajuan resign yang menunggu keputusan.',
            ]);
        }

        $initialManager = $user->isStaff() ? $user->manager() : null;
        if ($user->isStaff() && ! $initialManager) {
            throw ValidationException::withMessages([
                'approval' => 'Atasan langsung belum terdaftar. Hubungi HRD sebelum mengajukan resign.',
            ]);
        }

        $resignation = ResignationRequest::query()->create([
            'user_id' => $user->id,
            'last_working_date' => $validated['last_working_date'],
            'reason' => $validated['reason'],
            'handover_notes' => $validated['handover_notes'] ?? null,
            'status' => $initialManager ? 'pending_manager' : 'pending_ceo',
        ]);

        $approval = $this->workflowService->createRequest(
            type: 'resignation',
            subject: $resignation,
            requester: $user,
            division: $user->divisionKey(),
            initialApprover: $initialManager,
            payload: ['last_working_date' => $validated['last_working_date']],
        );

        return response()->json([
            'success' => true,
            'message' => $approval->status === 'pending_manager'
                ? 'Pengajuan resign telah dikirim kepada manager dan ditembuskan ke HRD.'
                : 'Pengajuan resign telah dikirim kepada CEO dan ditembuskan ke HRD.',
            'approval_id' => $approval->id,
            'status' => $approval->status,
        ], 201);
    }

    public function update(Request $request, ResignationRequest $resignationRequest)
    {
        $actor = $request->user();
        abort_unless(
            $resignationRequest->user_id === $actor->id
                && in_array($resignationRequest->status, ['pending_manager', 'pending_ceo'], true),
            403,
            'Pengajuan resign hanya dapat diedit oleh pembuat selama masih menunggu keputusan.',
        );

        $validated = $request->validate([
            'last_working_date' => ['required', 'date', 'after_or_equal:today'],
            'reason' => ['required', 'string', 'max:3000'],
            'handover_notes' => ['nullable', 'string', 'max:3000'],
        ]);
        $resignationRequest->forceFill($validated)->save();

        $approval = $resignationRequest->approvalRequest()
            ->whereIn('status', ['pending_manager', 'pending_ceo'])
            ->latest('id')
            ->first();
        if ($approval) {
            $approval->forceFill([
                'payload' => [
                    'last_working_date' => $validated['last_working_date'],
                    'edited_at' => now()->toIso8601String(),
                ],
            ])->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan resign berhasil diperbarui tanpa mengubah antrean approval.',
            'resignation' => $resignationRequest->fresh(),
        ]);
    }
}
