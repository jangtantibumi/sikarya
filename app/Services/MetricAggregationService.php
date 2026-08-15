<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Attendance;
use App\Models\ClientInflow;
use App\Models\Goal;
use App\Models\Kpi;
use App\Models\KpiPlan;
use App\Models\Lead;
use App\Models\MetricSnapshot;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class MetricAggregationService
{
    public function recalculateKpi(Kpi $kpi): void
    {
        $kpi->loadMissing('plan.goal', 'plan.manager');
        $currentValue = $this->resolveCurrentValue($kpi);

        $kpi->forceFill(['current_value' => round($currentValue, 2)])->save();
        $this->takeSnapshot($kpi, [
            'current_value' => round($currentValue, 2),
            'target_value' => (float) $kpi->target_value,
            'achievement' => $this->achievement($kpi),
        ]);

        $this->recalculateKpiPlan($kpi->plan);
    }

    public function recalculateKpiPlan(KpiPlan $plan): void
    {
        $plan->loadMissing('kpis', 'goal');

        $score = 0.0;
        if ($plan->status === 'approved') {
            foreach ($plan->kpis as $kpi) {
                $score += $this->achievement($kpi) * ((float) $kpi->weight / 100);
            }
        }

        $score = round(min(100, max(0, $score)), 2);
        $plan->forceFill(['score' => $score])->save();

        $this->takeSnapshot($plan, [
            'total_score' => $score,
            'status' => $plan->status,
        ]);

        if ($plan->goal) {
            $this->recalculateGoal($plan->goal);
        }
    }

    public function recalculateGoal(Goal $goal): void
    {
        $score = (float) $goal->kpiPlans()
            ->where('status', 'approved')
            ->avg('score');

        $goal->forceFill(['progress' => round($score, 2)])->save();

        $this->takeSnapshot($goal, [
            'progress' => round($score, 2),
            'division' => $goal->division,
        ]);
    }

    public function recalculateForDataSource(string $dataSource, ?string $division = null): void
    {
        Kpi::query()
            ->where('data_source', $dataSource)
            ->when($division, fn (Builder $query) => $query->whereHas(
                'plan',
                fn (Builder $plan) => $plan
                    ->where('division', $division)
                    ->orWhereHas('goal', fn (Builder $goal) => $goal->where('division', $division))
            ))
            ->with('plan.goal', 'plan.manager')
            ->each(fn (Kpi $kpi) => $this->recalculateKpi($kpi));
    }

    public function recalculateAll(): void
    {
        Kpi::query()
            ->with('plan.goal', 'plan.manager')
            ->each(fn (Kpi $kpi) => $this->recalculateKpi($kpi));
    }

    private function resolveCurrentValue(Kpi $kpi): float
    {
        return match ($kpi->data_source) {
            'leads' => $this->aggregateLeads($kpi),
            'client_inflows' => $this->aggregateClientInflows($kpi),
            'attendance' => $this->aggregateAttendance($kpi),
            default => $this->aggregateTasks($kpi),
        };
    }

    private function aggregateTasks(Kpi $kpi): float
    {
        $verified = $kpi->tasks()->where('status', 'verified');

        return match ($kpi->aggregation_type) {
            'sum' => (float) ((clone $verified)->whereNotNull('metric_value')->sum('metric_value')
                ?: (clone $verified)->count()),
            'average' => (float) ((clone $verified)->whereNotNull('metric_value')->avg('metric_value') ?? 0),
            'percentage' => $this->taskCompletionPercentage($kpi),
            'manual' => (float) $kpi->current_value,
            default => (float) (clone $verified)->count(),
        };
    }

    private function taskCompletionPercentage(Kpi $kpi): float
    {
        $eligible = $kpi->tasks()
            ->whereNotIn('status', ['pending_manager', 'rejected', 'cancelled'])
            ->count();

        if ($eligible === 0) {
            return 0;
        }

        $verified = $kpi->tasks()->where('status', 'verified')->count();

        return ($verified / $eligible) * 100;
    }

    private function aggregateLeads(Kpi $kpi): float
    {
        $userIds = $this->divisionUsers($kpi)->pluck('id');
        $query = Lead::query()->whereIn('assigned_to', $userIds);

        return match ($kpi->aggregation_type) {
            'sum' => (float) $query->sum('project_value'),
            'average' => (float) ($query->avg('project_value') ?? 0),
            'percentage' => $this->statusPercentage($query, 'Deal'),
            'manual' => (float) $kpi->current_value,
            default => (float) $query->count(),
        };
    }

    private function aggregateClientInflows(Kpi $kpi): float
    {
        $usernames = $this->divisionUsers($kpi)->pluck('username');
        $query = ClientInflow::query()->whereIn('created_by', $usernames);

        return match ($kpi->aggregation_type) {
            'sum' => (float) $query->sum('payment_amount'),
            'average' => (float) ($query->avg('payment_amount') ?? 0),
            'percentage' => $this->statusPercentage($query, 'LUNAS', 'payment_status'),
            'manual' => (float) $kpi->current_value,
            default => (float) $query->count(),
        };
    }

    private function aggregateAttendance(Kpi $kpi): float
    {
        $query = Attendance::query()
            ->whereIn('user_id', $this->divisionUsers($kpi)->pluck('id'))
            ->whereBetween('clock_in', [now()->startOfMonth(), now()->endOfMonth()]);

        if ($kpi->aggregation_type === 'average') {
            $durations = $query->whereNotNull('clock_out')->get()->map(
                fn (Attendance $attendance) => $attendance->clock_in->diffInMinutes($attendance->clock_out) / 60
            );

            return (float) ($durations->avg() ?? 0);
        }

        if ($kpi->aggregation_type === 'percentage') {
            $all = (clone $query)->count();
            if ($all === 0) {
                return 0;
            }

            $onTime = (clone $query)->where('status', 'Present')->count();

            return ($onTime / $all) * 100;
        }

        return $kpi->aggregation_type === 'manual'
            ? (float) $kpi->current_value
            : (float) $query->count();
    }

    private function divisionUsers(Kpi $kpi): Collection
    {
        $manager = $kpi->plan->manager;
        if (! $manager) {
            return collect();
        }

        return User::query()
            ->where('id', $manager->id)
            ->orWhere('parent', $manager->username)
            ->get();
    }

    private function statusPercentage(Builder $query, string $value, string $column = 'status'): float
    {
        $total = (clone $query)->count();
        if ($total === 0) {
            return 0;
        }

        return ((clone $query)->where($column, $value)->count() / $total) * 100;
    }

    private function achievement(Kpi $kpi): float
    {
        $target = (float) $kpi->target_value;
        $current = (float) $kpi->current_value;

        if ($target <= 0) {
            return 0;
        }

        if ($kpi->direction === 'lower_is_better') {
            return $current <= $target
                ? 100
                : min(100, ($target / max($current, 0.0001)) * 100);
        }

        return min(100, max(0, ($current / $target) * 100));
    }

    private function takeSnapshot($subject, array $data): void
    {
        MetricSnapshot::query()->updateOrCreate(
            [
                'subject_type' => $subject->getMorphClass(),
                'subject_id' => $subject->getKey(),
                'snapshot_date' => today(),
            ],
            ['data' => $data],
        );
    }
}
