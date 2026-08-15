<?php

declare(strict_types=1);

namespace Modules\Finance\Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;
use Modules\Finance\Models\PaymentTerm;

class FinancePaymentTermSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();
        if (! $company) {
            return;
        }

        $terms = [
            [
                'code' => 'COD',
                'name' => 'Cash On Delivery',
                'net_days' => 0,
                'discount_days' => 0,
                'discount_percentage' => 0.00,
                'description' => 'Pembayaran tunai saat barang diterima',
            ],
            [
                'code' => 'NET30',
                'name' => 'Net 30 Days',
                'net_days' => 30,
                'discount_days' => 0,
                'discount_percentage' => 0.00,
                'description' => 'Pelunasan jatuh tempo dalam 30 hari',
            ],
            [
                'code' => '2/10 NET30',
                'name' => '2% 10 Net 30',
                'net_days' => 30,
                'discount_days' => 10,
                'discount_percentage' => 2.00,
                'description' => 'Diskon 2% jika lunas dalam 10 hari, jatuh tempo 30 hari',
            ],
        ];

        foreach ($terms as $pt) {
            PaymentTerm::updateOrCreate(
                ['company_id' => $company->id, 'code' => $pt['code']],
                array_merge($pt, ['company_id' => $company->id, 'is_active' => true])
            );
        }
    }
}
