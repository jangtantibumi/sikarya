<?php

namespace Modules\Finance\Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;
use Modules\Finance\Models\AccountGroup;

class FinanceAccountGroupSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();
        if (! $company) {
            return;
        }

        $groups = [
            [
                'code' => '1100',
                'name' => 'Aset Lancar (Current Assets)',
                'category' => 'asset',
                'code_from' => '1100',
                'code_to' => '1199',
                'report_type' => 'balance_sheet',
            ],
            [
                'code' => '1200',
                'name' => 'Aset Tetap (Fixed Assets)',
                'category' => 'asset',
                'code_from' => '1200',
                'code_to' => '1299',
                'report_type' => 'balance_sheet',
            ],
            [
                'code' => '2100',
                'name' => 'Kewajiban Jangka Pendek (Current Liabilities)',
                'category' => 'liability',
                'code_from' => '2100',
                'code_to' => '2199',
                'report_type' => 'balance_sheet',
            ],
            [
                'code' => '3100',
                'name' => 'Ekuitas Pemilik (Equity)',
                'category' => 'equity',
                'code_from' => '3100',
                'code_to' => '3199',
                'report_type' => 'balance_sheet',
            ],
            [
                'code' => '4100',
                'name' => 'Pendapatan Usaha (Revenue)',
                'category' => 'revenue',
                'code_from' => '4100',
                'code_to' => '4199',
                'report_type' => 'profit_loss',
            ],
            [
                'code' => '5100',
                'name' => 'Beban Operasional (Operating Expenses)',
                'category' => 'expense',
                'code_from' => '5100',
                'code_to' => '5999',
                'report_type' => 'profit_loss',
            ],
        ];

        foreach ($groups as $grp) {
            AccountGroup::updateOrCreate(
                ['company_id' => $company->id, 'code' => $grp['code']],
                array_merge($grp, ['company_id' => $company->id, 'is_active' => true])
            );
        }
    }
}
