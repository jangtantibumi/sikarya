<?php

namespace Modules\Finance\Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;
use Modules\Finance\Models\ProfitCenter;

class FinanceProfitCenterSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();
        if (! $company) {
            return;
        }

        $profitCenters = [
            [
                'code' => 'PC-RETAIL',
                'name' => 'SBU Division Retail Stores',
                'manager_name' => 'Hendrik P.',
                'segment' => 'B2C Retail',
            ],
            [
                'code' => 'PC-WHOLESALE',
                'name' => 'SBU Division Enterprise Wholesale',
                'manager_name' => 'Rina M.',
                'segment' => 'B2B Enterprise',
            ],
        ];

        foreach ($profitCenters as $pc) {
            ProfitCenter::updateOrCreate(
                ['company_id' => $company->id, 'code' => $pc['code']],
                array_merge($pc, ['company_id' => $company->id, 'is_active' => true])
            );
        }
    }
}
