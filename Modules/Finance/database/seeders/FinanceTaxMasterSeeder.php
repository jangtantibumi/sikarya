<?php

declare(strict_types=1);

namespace Modules\Finance\Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;
use Modules\Finance\Models\ChartOfAccount;
use Modules\Finance\Models\TaxMaster;

class FinanceTaxMasterSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();
        if (! $company) {
            return;
        }

        $apCoa = ChartOfAccount::where('company_id', $company->id)->where('code', '2110.01')->first();

        $taxes = [
            [
                'code' => 'PPN11',
                'name' => 'Pajak Pertambahan Nilai (PPN 11%)',
                'rate' => 11.00,
                'tax_type' => 'vat',
                'calculation_type' => 'exclusive',
                'chart_of_account_id' => $apCoa?->id,
            ],
            [
                'code' => 'PPH23',
                'name' => 'PPh Pasal 23 Jasa (2%)',
                'rate' => 2.00,
                'tax_type' => 'withholding',
                'calculation_type' => 'exclusive',
                'chart_of_account_id' => $apCoa?->id,
            ],
        ];

        foreach ($taxes as $tax) {
            TaxMaster::updateOrCreate(
                ['company_id' => $company->id, 'code' => $tax['code']],
                array_merge($tax, ['company_id' => $company->id, 'is_active' => true])
            );
        }
    }
}
