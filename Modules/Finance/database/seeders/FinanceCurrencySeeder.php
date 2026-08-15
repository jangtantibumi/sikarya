<?php

namespace Modules\Finance\Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;
use Modules\Finance\Models\Currency;

class FinanceCurrencySeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();
        if (! $company) {
            return;
        }

        $currencies = [
            [
                'code' => 'IDR',
                'name' => 'Rupiah Indonesia',
                'symbol' => 'Rp',
                'decimal_places' => 2,
                'is_base' => true,
                'is_active' => true,
            ],
            [
                'code' => 'USD',
                'name' => 'US Dollar',
                'symbol' => '$',
                'decimal_places' => 2,
                'is_base' => false,
                'is_active' => true,
            ],
            [
                'code' => 'EUR',
                'name' => 'Euro',
                'symbol' => '€',
                'decimal_places' => 2,
                'is_base' => false,
                'is_active' => true,
            ],
        ];

        foreach ($currencies as $curr) {
            Currency::updateOrCreate(
                ['company_id' => $company->id, 'code' => $curr['code']],
                array_merge($curr, ['company_id' => $company->id])
            );
        }
    }
}
