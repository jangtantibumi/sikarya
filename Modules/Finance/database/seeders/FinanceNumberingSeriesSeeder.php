<?php

namespace Modules\Finance\Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;
use Modules\Finance\Models\NumberingSeries;

class FinanceNumberingSeriesSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();
        if (! $company) {
            return;
        }

        $series = [
            [
                'module_code' => 'FINANCE',
                'document_type' => 'JOURNAL_ENTRY',
                'prefix' => 'JV-{YYYY}-{MM}-',
                'suffix' => null,
                'length' => 5,
                'current_number' => 0,
                'reset_cycle' => 'yearly',
                'sample_number' => 'JV-2026-08-00001',
            ],
            [
                'module_code' => 'FINANCE',
                'document_type' => 'TAX_INVOICE',
                'prefix' => 'FP-{YYYY}-',
                'suffix' => null,
                'length' => 6,
                'current_number' => 0,
                'reset_cycle' => 'yearly',
                'sample_number' => 'FP-2026-000001',
            ],
        ];

        foreach ($series as $ns) {
            NumberingSeries::updateOrCreate(
                [
                    'company_id' => $company->id,
                    'module_code' => $ns['module_code'],
                    'document_type' => $ns['document_type'],
                ],
                array_merge($ns, ['company_id' => $company->id, 'is_active' => true])
            );
        }
    }
}
