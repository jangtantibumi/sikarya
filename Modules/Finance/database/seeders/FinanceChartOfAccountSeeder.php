<?php

namespace Modules\Finance\Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;
use Modules\Finance\Models\AccountGroup;
use Modules\Finance\Models\ChartOfAccount;
use Modules\Finance\Models\Currency;

class FinanceChartOfAccountSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();
        if (! $company) {
            return;
        }

        $idr = Currency::where('company_id', $company->id)->where('code', 'IDR')->first();
        $assetGroup = AccountGroup::where('company_id', $company->id)->where('code', '1100')->first();
        $liabilityGroup = AccountGroup::where('company_id', $company->id)->where('code', '2100')->first();
        $equityGroup = AccountGroup::where('company_id', $company->id)->where('code', '3100')->first();
        $revenueGroup = AccountGroup::where('company_id', $company->id)->where('code', '4100')->first();
        $expenseGroup = AccountGroup::where('company_id', $company->id)->where('code', '5100')->first();

        // 1. Header Accounts
        $headerCash = ChartOfAccount::updateOrCreate(
            ['company_id' => $company->id, 'code' => '1110'],
            [
                'company_id' => $company->id,
                'account_group_id' => $assetGroup?->id,
                'name' => 'Kas dan Bank',
                'type' => 'asset',
                'balance_type' => 'debit',
                'currency_id' => $idr?->id,
                'is_header' => true,
                'is_reconciliation' => false,
                'is_active' => true,
            ]
        );

        // 2. Detail Accounts
        ChartOfAccount::updateOrCreate(
            ['company_id' => $company->id, 'code' => '1110.01'],
            [
                'company_id' => $company->id,
                'account_group_id' => $assetGroup?->id,
                'parent_id' => $headerCash->id,
                'name' => 'Kas Utama (IDR)',
                'type' => 'asset',
                'balance_type' => 'debit',
                'currency_id' => $idr?->id,
                'is_header' => false,
                'is_reconciliation' => false,
                'is_active' => true,
            ]
        );

        ChartOfAccount::updateOrCreate(
            ['company_id' => $company->id, 'code' => '1110.02'],
            [
                'company_id' => $company->id,
                'account_group_id' => $assetGroup?->id,
                'parent_id' => $headerCash->id,
                'name' => 'Bank Mandiri (IDR)',
                'type' => 'asset',
                'balance_type' => 'debit',
                'currency_id' => $idr?->id,
                'is_header' => false,
                'is_reconciliation' => true,
                'is_active' => true,
            ]
        );

        ChartOfAccount::updateOrCreate(
            ['company_id' => $company->id, 'code' => '2110.01'],
            [
                'company_id' => $company->id,
                'account_group_id' => $liabilityGroup?->id,
                'name' => 'Hutang Usaha (Accounts Payable)',
                'type' => 'liability',
                'balance_type' => 'credit',
                'currency_id' => $idr?->id,
                'is_header' => false,
                'is_reconciliation' => true,
                'is_active' => true,
            ]
        );

        ChartOfAccount::updateOrCreate(
            ['company_id' => $company->id, 'code' => '3110.01'],
            [
                'company_id' => $company->id,
                'account_group_id' => $equityGroup?->id,
                'name' => 'Modal Disetor (Paid-in Capital)',
                'type' => 'equity',
                'balance_type' => 'credit',
                'currency_id' => $idr?->id,
                'is_header' => false,
                'is_reconciliation' => false,
                'is_active' => true,
            ]
        );

        ChartOfAccount::updateOrCreate(
            ['company_id' => $company->id, 'code' => '4110.01'],
            [
                'company_id' => $company->id,
                'account_group_id' => $revenueGroup?->id,
                'name' => 'Pendapatan Penjualan Produk',
                'type' => 'revenue',
                'balance_type' => 'credit',
                'currency_id' => $idr?->id,
                'is_header' => false,
                'is_reconciliation' => false,
                'is_active' => true,
            ]
        );

        ChartOfAccount::updateOrCreate(
            ['company_id' => $company->id, 'code' => '5110.01'],
            [
                'company_id' => $company->id,
                'account_group_id' => $expenseGroup?->id,
                'name' => 'Beban Gaji dan Tunjangan',
                'type' => 'expense',
                'balance_type' => 'debit',
                'currency_id' => $idr?->id,
                'is_header' => false,
                'is_reconciliation' => false,
                'is_active' => true,
            ]
        );
    }
}
