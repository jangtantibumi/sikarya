<?php

declare(strict_types=1);

namespace Modules\Finance\Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;
use Modules\Finance\Models\CostCenter;

class FinanceCostCenterSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();
        if (! $company) {
            return;
        }

        $costCenters = [
            [
                'code' => 'CC-HQ',
                'name' => 'Headquarters Administrative',
                'manager_name' => 'Bambang S.',
                'department' => 'Corporate Affairs',
            ],
            [
                'code' => 'CC-IT',
                'name' => 'Information Technology',
                'manager_name' => 'Subadell',
                'department' => 'Technology & ERP',
            ],
            [
                'code' => 'CC-SALES',
                'name' => 'Sales & Marketing Operations',
                'manager_name' => 'Dewi A.',
                'department' => 'Commercial',
            ],
        ];

        foreach ($costCenters as $cc) {
            CostCenter::updateOrCreate(
                ['company_id' => $company->id, 'code' => $cc['code']],
                array_merge($cc, ['company_id' => $company->id, 'is_active' => true])
            );
        }
    }
}
