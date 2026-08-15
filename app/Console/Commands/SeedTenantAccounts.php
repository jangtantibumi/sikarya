<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\Company;
use Illuminate\Console\Command;

class SeedTenantAccounts extends Command
{
    protected $signature = 'app:seed-tenant-accounts';

    protected $description = 'Seed standard Chart of Accounts for all existing tenants and remove global accounts.';

    public function handle()
    {
        $companies = Company::all();
        $globalAccounts = Account::withoutGlobalScopes()->whereNull('company_id')->get();

        if ($globalAccounts->isEmpty()) {
            $this->info('No global accounts found to migrate.');
            // Generate standard ones if none exist
            $standardAccounts = [
                ['code' => '1100', 'name' => 'Kas & Bank', 'type' => 'asset', 'normal_balance' => 'debit', 'system_key' => 'cash_bank', 'is_active' => true],
                ['code' => '1200', 'name' => 'Piutang Usaha', 'type' => 'asset', 'normal_balance' => 'debit', 'system_key' => 'accounts_receivable', 'is_active' => true],
                ['code' => '2100', 'name' => 'Utang Usaha', 'type' => 'liability', 'normal_balance' => 'credit', 'system_key' => 'accounts_payable', 'is_active' => true],
                ['code' => '3100', 'name' => 'Modal & Saldo Laba', 'type' => 'equity', 'normal_balance' => 'credit', 'system_key' => 'retained_earnings', 'is_active' => true],
                ['code' => '4100', 'name' => 'Pendapatan Jasa Desain', 'type' => 'revenue', 'normal_balance' => 'credit', 'system_key' => 'design_revenue', 'is_active' => true],
                ['code' => '4200', 'name' => 'Pendapatan Kontraktor', 'type' => 'revenue', 'normal_balance' => 'credit', 'system_key' => 'contractor_revenue', 'is_active' => true],
                ['code' => '5100', 'name' => 'Biaya Langsung Proyek', 'type' => 'expense', 'normal_balance' => 'debit', 'system_key' => 'direct_project_cost', 'is_active' => true],
                ['code' => '6100', 'name' => 'Beban Gaji & SDM', 'type' => 'expense', 'normal_balance' => 'debit', 'system_key' => 'payroll_expense', 'is_active' => true],
                ['code' => '6200', 'name' => 'Beban Operasional', 'type' => 'expense', 'normal_balance' => 'debit', 'system_key' => 'operating_expense', 'is_active' => true],
                ['code' => '6300', 'name' => 'Beban Pemasaran', 'type' => 'expense', 'normal_balance' => 'debit', 'system_key' => 'marketing_expense', 'is_active' => true],
                ['code' => '1300', 'name' => 'Persediaan', 'type' => 'asset', 'normal_balance' => 'debit', 'system_key' => 'inventory', 'is_active' => true],
            ];
            $globalAccounts = collect($standardAccounts)->map(fn ($a) => (object) $a);
        } else {
            // Also add inventory account if missing
            if (! $globalAccounts->where('system_key', 'inventory')->first()) {
                $globalAccounts->push((object) [
                    'code' => '1300', 'name' => 'Persediaan', 'type' => 'asset', 'normal_balance' => 'debit', 'system_key' => 'inventory', 'is_active' => true,
                ]);
            }
        }

        foreach ($companies as $company) {
            foreach ($globalAccounts as $acc) {
                Account::withoutGlobalScopes()->updateOrCreate(
                    ['company_id' => $company->id, 'system_key' => $acc->system_key],
                    [
                        'code' => $acc->code,
                        'name' => $acc->name,
                        'type' => $acc->type,
                        'normal_balance' => $acc->normal_balance,
                        'is_active' => $acc->is_active,
                    ]
                );
            }
            $this->info("Seeded CoA for company: {$company->name}");
        }

        // Delete global accounts
        Account::withoutGlobalScopes()->whereNull('company_id')->delete();
        $this->info('Global accounts removed.');
    }
}
