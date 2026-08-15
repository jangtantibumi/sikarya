<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Goal;
use App\Models\KpiPlan;
use App\Services\MetricAggregationService;
use Illuminate\Http\Request;

class DashboardMetricController extends Controller
{
    public function __construct(
        private readonly MetricAggregationService $metrics,
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $this->metrics->recalculateAll();

        $goals = Goal::query()
            ->with(['kpiPlans' => fn ($plans) => $plans->where('status', 'approved')->with('kpis', 'manager:id,name,username')])
            ->when(! $user->isCEO(), fn ($query) => $query->where('division', $user->divisionKey()))
            ->where('status', 'active')
            ->get();

        $plans = KpiPlan::query()
            ->with(['goal:id,title,division,progress', 'manager:id,name,username,role', 'kpis'])
            ->where('status', 'approved')
            ->when(! $user->isCEO(), fn ($query) => $query->whereHas(
                'goal',
                fn ($goal) => $goal->where('division', $user->divisionKey())
            ))
            ->get();

        return response()->json([
            'generated_at' => now()->toIso8601String(),
            'goals' => $goals,
            'plans' => $plans,
            'division_scores' => $goals
                ->groupBy('division')
                ->map(fn ($items) => round((float) $items->avg('progress'), 2)),
        ]);
    }
}
