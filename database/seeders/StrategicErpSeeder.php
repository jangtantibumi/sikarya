<?php

namespace Database\Seeders;

use App\Models\ClientInflow;
use App\Services\AccountingService;
use App\Services\ProjectCostingService;
use Illuminate\Database\Seeder;

class StrategicErpSeeder extends Seeder
{
    public function run(): void
    {
        $projects = app(ProjectCostingService::class);
        $accounting = app(AccountingService::class);

        ClientInflow::query()
            ->orderBy('id')
            ->each(function (ClientInflow $inflow) use ($projects, $accounting): void {
                $projects->syncClientInflow($inflow);
                $accounting->syncClientInflow($inflow);
            });
    }
}
