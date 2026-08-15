<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TalentReview;
use App\Models\User;
use App\Services\SecurityAuditService;
use App\Services\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class TalentController extends Controller
{
    public function __construct(private readonly SecurityAuditService $audit, private readonly TenantContext $tenant)
    {
    }

    public function index(Request $request)
    {
        $viewer = $request->user();
        $year = (int) $request->integer('year', now()->year);
        $visibleUserIds = $this->visibleUsers($viewer)->pluck('id');

        $reviews = TalentReview::query()
            ->with([
                'user:id,name,username,role,job_title,parent',
                'reviewer:id,name,username,role,job_title',
            ])
            ->whereIn('user_id', $visibleUserIds)
            ->where('review_year', $year)
            ->when(
                $viewer->isStaff(),
                fn (Builder $query) => $query->where('status', 'published'),
            )
            ->latest('updated_at')
            ->get();

        return response()->json([
            'year' => $year,
            'can_manage' => $viewer->isCEO() || $viewer->isHRD() || $viewer->isManager(),
            'people' => $this->visibleUsers($viewer)
                ->map(fn (User $user): array => $user->only([
                    'id', 'name', 'username', 'role', 'job_title', 'parent',
                ]))
                ->values(),
            'reviews' => $reviews,
            'summary' => [
                'reviewed' => $reviews->where('status', 'published')->count(),
                'average_performance' => round((float) ($reviews->where('status', 'published')->avg('performance_score') ?? 0), 2),
                'average_potential' => round((float) ($reviews->where('status', 'published')->avg('potential_score') ?? 0), 2),
                'development_plans' => $reviews->whereNotNull('development_plan')->count(),
            ],
            'server_time' => now()->toIso8601String(),
        ]);
    }

    public function store(Request $request)
    {
        $actor = $request->user();
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'review_year' => ['required', 'integer', 'between:2020,2100'],
            'review_cycle' => ['required', 'string', 'in:quarter_1,quarter_2,quarter_3,quarter_4,semester_1,semester_2,annual'],
            'performance_score' => ['required', 'numeric', 'between:0,100'],
            'potential_score' => ['required', 'numeric', 'between:0,100'],
            'competency_score' => ['required', 'numeric', 'between:0,100'],
            'readiness' => ['required', 'string', 'in:developing,ready_1_year,ready_now'],
            'status' => ['required', 'string', 'in:draft,published'],
            'strengths' => ['nullable', 'string', 'max:3000'],
            'development_plan' => ['nullable', 'string', 'max:5000'],
            'next_role' => ['nullable', 'string', 'max:160'],
            'training_plan' => ['nullable', 'array', 'max:20'],
            'training_plan.*' => ['string', 'max:255'],
        ]);

        $employee = User::query()
            ->whereKey($validated['user_id'])
            ->where('is_active', true)
            ->where('account_status', 'active')
            ->firstOrFail();
        abort_unless($this->canManage($actor, $employee), 403);

        $review = TalentReview::query()->updateOrCreate(
            [
                'user_id' => $employee->id,
                'review_year' => $validated['review_year'],
                'review_cycle' => $validated['review_cycle'],
            ],
            [
                ...$validated,
                'reviewer_id' => $actor->id,
                'published_at' => $validated['status'] === 'published' ? now() : null,
            ],
        );

        $this->audit->record(
            'talent.review_saved',
            actor: $actor,
            request: $request,
            metadata: [
                'employee' => $employee->username,
                'year' => $review->review_year,
                'cycle' => $review->review_cycle,
                'status' => $review->status,
            ],
            subjectType: TalentReview::class,
            subjectId: $review->id,
        );

        return response()->json([
            'success' => true,
            'message' => $review->status === 'published'
                ? 'Review talent berhasil dipublikasikan kepada karyawan terkait.'
                : 'Draft review talent berhasil disimpan.',
            'review' => $review->fresh(['user', 'reviewer']),
        ], $review->wasRecentlyCreated ? 201 : 200);
    }

    private function visibleUsers(User $viewer)
    {
        $query = User::query()->where('is_active', true)->where('account_status', 'active');
        if ($this->tenant->id()) {
            $query->where('company_id', $this->tenant->id());
        }

        if ($viewer->isCEO() || $viewer->isHRD()) {
            return $query->orderBy('name')->get();
        }

        if ($viewer->isManager()) {
            return $query
                ->where(fn (Builder $users) => $users
                    ->whereKey($viewer->id)
                    ->orWhere('parent', $viewer->username))
                ->orderBy('name')
                ->get();
        }

        return $query->whereKey($viewer->id)->get();
    }

    private function canManage(User $actor, User $employee): bool
    {
        if ($actor->isCEO() || $actor->isHRD()) {
            return true;
        }

        return $actor->isManager() && $actor->isManagerOf($employee);
    }
}
