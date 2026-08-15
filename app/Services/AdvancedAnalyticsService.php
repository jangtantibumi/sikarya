<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Attendance;
use App\Models\ClientInflow;
use App\Models\Goal;
use App\Models\Lead;
use App\Models\Product;
use App\Models\ProductionOrder;
use App\Models\ProductionWaste;
use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\TalentReview;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class AdvancedAnalyticsService
{
    public function __construct(
        private readonly AccountingService $accounting,
        private readonly ProjectCostingService $projectCosting,
    ) {}

    public function overview(User $viewer, int $year): array
    {
        $visibleUsers = $this->visibleUsers($viewer);
        $visibleUserIds = $visibleUsers->pluck('id');
        $financialVisible = $viewer->isCEO() || $viewer->divisionKey() === 'finance';
        $projectVisible = $viewer->isCEO() || in_array($viewer->divisionKey(), ['operasional', 'finance'], true);
        $projects = $projectVisible
            ? Project::query()->with('costs')->orderByDesc('contract_value')->get()
            : collect();
        $publishedReviews = TalentReview::query()
            ->whereIn('user_id', $visibleUserIds)
            ->where('status', 'published')
            ->where('review_year', $year);
        $taskQuery = Task::query()->whereIn('user_id', $visibleUserIds);
        $eligibleTasks = (clone $taskQuery)->whereNotIn('status', ['rejected', 'cancelled'])->count();
        $completedTasks = (clone $taskQuery)->whereIn('status', ['done', 'verified'])->count();
        $attendanceQuery = Attendance::query()
            ->whereIn('user_id', $visibleUserIds)
            ->whereYear('clock_in', $year);
        $attendanceCount = (clone $attendanceQuery)->count();
        $onTimeCount = (clone $attendanceQuery)->whereIn('status', ['Present', 'present'])->count();
        $annualFinance = $financialVisible
            ? $this->accounting->annualEvaluation($year)
            : null;

        $projectPortfolio = $projects->map(function (Project $project): array {
            return [
                'id' => $project->id,
                'code' => $project->code,
                'name' => $project->name,
                'type' => $project->project_type,
                'status' => $project->status,
                'progress' => (float) $project->progress,
                'contract_value' => (float) $project->contract_value,
                'budget_cost' => (float) $project->budget_cost,
                ...$this->projectCosting->summary($project),
            ];
        });

        $alerts = collect();
        $projectPortfolio->each(function (array $project) use ($alerts): void {
            if ($project['cost_utilization'] >= 85 && $project['progress'] < 80) {
                $alerts->push([
                    'severity' => 'high',
                    'title' => 'Risiko biaya proyek',
                    'message' => "{$project['name']} telah memakai {$project['cost_utilization']}% anggaran saat progres {$project['progress']}%.",
                ]);
            }
        });
        $overdue = (clone $taskQuery)
            ->whereNotIn('status', ['done', 'verified', 'cancelled'])
            ->whereNotNull('deadline')
            ->where('deadline', '<', now())
            ->count();
        if ($overdue > 0) {
            $alerts->push([
                'severity' => 'medium',
                'title' => 'Task melewati tenggat',
                'message' => "{$overdue} task dalam cakupan Anda belum selesai setelah deadline.",
            ]);
        }
        if ($financialVisible) {
            $outstanding = (float) ClientInflow::query()
                ->where('payment_status', 'Belum Lunas')
                ->sum('remaining_balance');
            if ($outstanding > 0) {
                $alerts->push([
                    'severity' => 'medium',
                    'title' => 'Piutang klien',
                    'message' => 'Saldo pembayaran klien yang belum lunas: Rp '.number_format($outstanding, 0, ',', '.').'.',
                ]);
            }
        }

        return [
            'generated_at' => now()->toIso8601String(),
            'scope' => $viewer->isCEO() ? 'company' : ($viewer->isManager() ? 'division_team' : 'personal'),
            'year' => $year,
            'financial_visible' => $financialVisible,
            'financial' => $annualFinance,
            'people' => [
                'active_people' => $visibleUsers->count(),
                'reviewed_people' => (clone $publishedReviews)->distinct('user_id')->count('user_id'),
                'average_performance' => round((float) ((clone $publishedReviews)->avg('performance_score') ?? 0), 2),
                'average_potential' => round((float) ((clone $publishedReviews)->avg('potential_score') ?? 0), 2),
                'high_potential' => (clone $publishedReviews)->where('potential_score', '>=', 80)->count(),
            ],
            'execution' => [
                'task_completion_rate' => $eligibleTasks > 0 ? round(($completedTasks / $eligibleTasks) * 100, 2) : 0,
                'overdue_tasks' => $overdue,
                'attendance_on_time_rate' => $attendanceCount > 0 ? round(($onTimeCount / $attendanceCount) * 100, 2) : 0,
                'goal_progress' => round((float) Goal::query()
                    ->when(! $viewer->isCEO(), fn (Builder $query) => $query->where('division', $viewer->divisionKey()))
                    ->where('status', 'active')
                    ->avg('progress'), 2),
            ],
            'projects' => [
                'visible' => $projectVisible,
                'total' => $projectPortfolio->count(),
                'design' => $projectPortfolio->where('type', 'design')->count(),
                'contractor' => $projectPortfolio->where('type', 'contractor')->count(),
                'portfolio' => $projectPortfolio->values(),
            ],
            'crm' => $this->getCrmMetrics(),
            'purchasing' => $this->getPurchasingMetrics(),
            'inventory' => $this->getInventoryMetrics(),
            'production' => $this->getProductionMetrics(),
            'alerts' => $alerts->values(),
        ];
    }

    private function visibleUsers(User $viewer)
    {
        if ($viewer->isCEO() || $viewer->isHRD()) {
            return User::query()->where('is_active', true)->where('account_status', 'active')->get();
        }

        if ($viewer->isManager()) {
            return User::query()
                ->where('is_active', true)
                ->where('account_status', 'active')
                ->where(fn (Builder $query) => $query->whereKey($viewer->id)->orWhere('parent', $viewer->username))
                ->get();
        }

        return User::query()->whereKey($viewer->id)->get();
    }

    private function getCrmMetrics(): array
    {
        $leads = Lead::query()->get();
        $won = $leads->where('status', 'deal')->count();
        $closed = $won + $leads->where('status', 'lost')->count();

        return [
            'total_leads' => $leads->count(),
            'open_pipeline_value' => (float) $leads->whereIn('status', ['leads', 'penawaran'])->sum('project_value'),
            'won_value' => (float) $leads->where('status', 'deal')->sum('project_value'),
            'conversion_rate' => $closed > 0 ? round(($won / $closed) * 100, 2) : 0,
        ];
    }

    private function getPurchasingMetrics(): array
    {
        $pos = PurchaseOrder::query()->get();

        return [
            'total_orders' => $pos->count(),
            'total_value' => (float) $pos->sum('total_amount'),
            'pending_receipts' => $pos->whereIn('status', ['approved', 'partially_received'])->count(),
        ];
    }

    private function getInventoryMetrics(): array
    {
        // Simplification for analytics: sum of product standard_cost * some estimated qty
        // Or if we have a view for current stock, we can use it.
        $products = Product::query()->get();

        return [
            'total_products' => $products->count(),
            'estimated_valuation' => (float) $products->sum('standard_cost'), // Placeholder for actual stock * cost
        ];
    }

    private function getProductionMetrics(): array
    {
        $orders = ProductionOrder::query()->get();
        $totalOutput = $orders->sum('planned_quantity');

        // Count waste
        $waste = ProductionWaste::query()->sum('quantity');

        return [
            'total_orders' => $orders->count(),
            'active_orders' => $orders->whereIn('status', ['planned', 'in_progress'])->count(),
            'defect_rate' => $totalOutput > 0 ? round(($waste / $totalOutput) * 100, 2) : 0,
        ];
    }
}
