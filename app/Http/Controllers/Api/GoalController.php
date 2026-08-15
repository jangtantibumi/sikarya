<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Goal;
use App\Services\WorkflowNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class GoalController extends Controller
{
    public function __construct(
        private readonly WorkflowNotificationService $notifications,
    ) {}

    public function index(Request $request)
    {
        Gate::authorize('viewAny', Goal::class);

        $user = $request->user();
        $query = Goal::query()->with([
            'creator:id,name,username',
            'kpiPlans' => fn ($plans) => $plans->with(['manager:id,name,username,role', 'kpis']),
        ]);

        if (! $user->isCEO()) {
            $query->where('division', $user->divisionKey());
        }

        if (! $request->boolean('include_inactive')) {
            $query->where('status', 'active');
        }

        return response()->json($query->orderByDesc('year')->orderByDesc('id')->get());
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Goal::class);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:3000'],
            'division' => ['required', 'string', 'in:marketing,operasional,finance,hrd'],
            'year' => ['required', 'integer', 'between:2020,2100'],
        ]);

        $goal = Goal::query()->create([
            ...$validated,
            'status' => 'active',
            'progress' => 0,
            'created_by' => $request->user()->id,
        ]);

        $this->notifications->send(
            $this->notifications->managersForDivision($goal->division),
            'Goal divisi baru',
            "CEO menetapkan goal \"{$goal->title}\". Susun rencana KPI dengan total bobot 100% untuk diajukan.",
            "goal:{$goal->id}:created:manager",
            'goal',
            '/#setup',
            ['goal_id' => $goal->id, 'division' => $goal->division],
        );

        return response()->json([
            'success' => true,
            'message' => 'Goal divisi berhasil dibuat dan manager terkait telah diberi notifikasi.',
            'goal' => $goal->load('creator:id,name,username'),
        ], 201);
    }

    public function update(Request $request, Goal $goal)
    {
        Gate::authorize('update', $goal);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:3000'],
            'division' => ['required', 'string', 'in:marketing,operasional,finance,hrd'],
            'year' => ['required', 'integer', 'between:2020,2100'],
            'status' => ['sometimes', 'string', 'in:active,inactive'],
        ]);

        $goal->forceFill($validated)->save();
        $this->notifications->send(
            $this->notifications->managersForDivision($goal->division),
            'Goal divisi diperbarui',
            "CEO memperbarui goal \"{$goal->title}\". Tinjau kembali kesesuaian rencana KPI divisi.",
            "goal:{$goal->id}:updated:".now()->format('YmdHis'),
            'goal',
            '/#setup',
            ['goal_id' => $goal->id, 'division' => $goal->division],
        );

        return response()->json([
            'success' => true,
            'message' => 'Goal berhasil diperbarui dan manager terkait telah diberi notifikasi.',
            'goal' => $goal->fresh(['creator:id,name,username', 'kpiPlans.kpis']),
        ]);
    }
}
