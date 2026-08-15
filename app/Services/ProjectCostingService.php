<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ClientInflow;
use App\Models\Project;
use Illuminate\Support\Str;

class ProjectCostingService
{
    public function syncClientInflow(ClientInflow $inflow): Project
    {
        $identity = trim((string) ($inflow->client_no ?: $inflow->client_name.'-'.$inflow->start_project));
        $code = 'PRJ-'.Str::upper(substr(hash('sha256', Str::lower($identity)), 0, 10));
        $type = $this->projectType($inflow);
        $project = Project::query()->firstOrNew(['code' => $code]);

        $project->fill([
            'client_inflow_id' => $project->client_inflow_id ?: $inflow->id,
            'name' => ($type === 'contractor' ? 'Konstruksi ' : 'Desain ').$inflow->client_name,
            'client_name' => $inflow->client_name,
            'project_type' => $type,
            'status' => $project->status ?: 'active',
            'start_date' => $project->start_date ?: $inflow->date,
            'contract_value' => max((float) $project->contract_value, (float) $inflow->project_value),
            'budget_cost' => (float) $project->budget_cost,
            'progress' => (float) $project->progress,
            'notes' => $project->notes ?: $inflow->notes,
        ])->save();

        return $project;
    }

    public function summary(Project $project): array
    {
        $actualCost = round((float) $project->costs()->sum('amount'), 2);
        $contractValue = round((float) $project->contract_value, 2);
        $budget = round((float) $project->budget_cost, 2);
        $margin = round($contractValue - $actualCost, 2);

        return [
            'actual_cost' => $actualCost,
            'remaining_budget' => max(0, round($budget - $actualCost, 2)),
            'cost_utilization' => $budget > 0 ? round(($actualCost / $budget) * 100, 2) : 0,
            'estimated_margin' => $margin,
            'estimated_margin_percentage' => $contractValue > 0 ? round(($margin / $contractValue) * 100, 2) : 0,
        ];
    }

    private function projectType(ClientInflow $inflow): string
    {
        $text = Str::lower(implode(' ', [$inflow->package, $inflow->notes]));

        return Str::contains($text, ['kontraktor', 'construction', 'konstruksi', 'pelaksana'])
            ? 'contractor'
            : 'design';
    }
}
