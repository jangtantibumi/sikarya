<?php

namespace Modules\Finance\Database\Seeders;

use Illuminate\Database\Seeder;

class FinanceDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            FinanceAccountGroupSeeder::class,
            FinanceCurrencySeeder::class,
            FinanceChartOfAccountSeeder::class,
            FinanceFiscalYearSeeder::class,
            FinanceCostCenterSeeder::class,
            FinanceProfitCenterSeeder::class,
            FinanceTaxMasterSeeder::class,
            FinancePaymentTermSeeder::class,
            FinanceNumberingSeriesSeeder::class,
        ]);
    }
}
