<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Account;

class DataMigrationSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->delete();

        $defaultUsers = [
            ['username' => 'ceo', 'name' => 'Super Admin', 'role' => 'ceo', 'parent' => null, 'employment_type' => 'Full-Time', 'email' => 'admin@suba-arch.co.id'],
            ['username' => 'mgr_marketing', 'name' => 'Maulana Mkt', 'role' => 'mgr_marketing', 'parent' => 'ceo', 'employment_type' => 'Full-Time'],
            ['username' => 'maulana', 'name' => 'M. Maulana Zakaria', 'role' => 'staff_marketing', 'parent' => 'mgr_marketing', 'employment_type' => 'Full-Time'],
            ['username' => 'dbest', 'name' => 'D BEST AR', 'role' => 'staff_marketing', 'parent' => 'mgr_marketing', 'employment_type' => 'Full-Time'],
            ['username' => 'mgr_ops', 'name' => 'Reza Ops', 'role' => 'mgr_ops', 'parent' => 'ceo', 'employment_type' => 'Full-Time'],
            ['username' => 'staff_ops', 'name' => 'Budi Ops', 'role' => 'staff_ops', 'parent' => 'mgr_ops', 'employment_type' => 'Full-Time'],
            ['username' => 'mgr_finance', 'name' => 'Hendra Fin', 'role' => 'mgr_finance', 'parent' => 'ceo', 'employment_type' => 'Full-Time'],
            ['username' => 'staff_finance', 'name' => 'Siti Fin', 'role' => 'staff_finance', 'parent' => 'mgr_finance', 'employment_type' => 'Full-Time'],
            ['username' => 'sonia', 'name' => 'Sonia HRD', 'role' => 'mgr_hrd', 'parent' => 'ceo', 'employment_type' => 'Full-Time'],
        ];

        foreach ($defaultUsers as $u) {
            User::updateOrCreate(
                ['username' => $u['username']],
                [
                    'name' => $u['name'],
                    'role' => $u['role'],
                    'parent' => $u['parent'],
                    'employment_type' => $u['employment_type'],
                    'email' => $u['email'] ?? ($u['username'] . '@suba-arch.local'),
                    'password' => Hash::make(Str::random(64)),
                    'is_active' => true,
                    'account_status' => 'active',
                    'archived_at' => null,
                ]
            );
        }

        $accounts = [
            ['code' => '1001', 'name' => 'Kas & Bank', 'type' => 'asset', 'system_key' => 'cash_bank'],
            ['code' => '1101', 'name' => 'Persediaan', 'type' => 'asset', 'system_key' => 'inventory'],
            ['code' => '2001', 'name' => 'Hutang Dagang', 'type' => 'liability', 'system_key' => 'accounts_payable'],
            ['code' => '4001', 'name' => 'Pendapatan Desain', 'type' => 'revenue', 'system_key' => 'design_revenue'],
            ['code' => '4002', 'name' => 'Pendapatan Konstruksi', 'type' => 'revenue', 'system_key' => 'contractor_revenue'],
            ['code' => '5001', 'name' => 'Beban Pekerja Desain', 'type' => 'expense', 'system_key' => 'design_labor'],
            ['code' => '5002', 'name' => 'Beban Operasional', 'type' => 'expense', 'system_key' => 'operating_expense'],
        ];

        foreach ($accounts as $acc) {
            Account::firstOrCreate(
                ['system_key' => $acc['system_key']],
                [
                    'code' => $acc['code'],
                    'name' => $acc['name'],
                    'type' => $acc['type'],
                    'normal_balance' => in_array($acc['type'], ['asset', 'expense']) ? 'debit' : 'credit',
                    'is_active' => true,
                ]
            );
        }

        $this->command->info('Data migrated additively (idempotent).');
    }
}
