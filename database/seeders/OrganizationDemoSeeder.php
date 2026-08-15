<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Company;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class OrganizationDemoSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first() ?? Company::create(['name' => 'PT Northstar Teknologi', 'code' => 'NTS']);

        $defaultPassword = Hash::make('Northstar123!');

        // 1. CEO
        $ceo = User::firstOrCreate(
            ['username' => 'ceo.demo'],
            [
                'name' => 'Budi Santoso',
                'email' => 'ceo@demo.com',
                'password' => $defaultPassword,
                'role' => 'ceo',
                'job_title' => 'Chief Executive Officer',
                'employment_type' => 'Full-Time',
                'division' => 'company',
                'company_id' => $company->id,
                'is_active' => true,
                'account_status' => 'active',
                'base_salary' => 50000000,
            ]
        );

        // 2. Directors
        $dirOps = User::firstOrCreate(
            ['username' => 'dir.ops'],
            [
                'name' => 'Siska Andarini',
                'email' => 'ops@demo.com',
                'password' => $defaultPassword,
                'role' => 'mgr_ops',
                'job_title' => 'Chief Operating Officer',
                'employment_type' => 'Full-Time',
                'division' => 'operasional',
                'parent' => $ceo->username,
                'company_id' => $company->id,
                'is_active' => true,
                'account_status' => 'active',
                'base_salary' => 35000000,
            ]
        );

        $dirFin = User::firstOrCreate(
            ['username' => 'dir.fin'],
            [
                'name' => 'Andi Wijaya',
                'email' => 'finance@demo.com',
                'password' => $defaultPassword,
                'role' => 'mgr_finance',
                'job_title' => 'Chief Financial Officer',
                'employment_type' => 'Full-Time',
                'division' => 'finance',
                'parent' => $ceo->username,
                'company_id' => $company->id,
                'is_active' => true,
                'account_status' => 'active',
                'base_salary' => 35000000,
            ]
        );

        $dirHR = User::firstOrCreate(
            ['username' => 'dir.hr'],
            [
                'name' => 'Diana Putri',
                'email' => 'hrd@demo.com',
                'password' => $defaultPassword,
                'role' => 'mgr_hrd',
                'job_title' => 'HR Director',
                'employment_type' => 'Full-Time',
                'division' => 'hrd',
                'parent' => $ceo->username,
                'company_id' => $company->id,
                'is_active' => true,
                'account_status' => 'active',
                'base_salary' => 30000000,
            ]
        );

        // 3. Managers
        $mgrProduction = User::firstOrCreate(
            ['username' => 'mgr.prod'],
            [
                'name' => 'Rahmat Hidayat',
                'email' => 'prod@demo.com',
                'password' => $defaultPassword,
                'role' => 'mgr_ops',
                'job_title' => 'Production Manager',
                'employment_type' => 'Full-Time',
                'division' => 'operasional',
                'parent' => $dirOps->username,
                'company_id' => $company->id,
                'is_active' => true,
                'account_status' => 'active',
                'base_salary' => 20000000,
            ]
        );

        $mgrWarehouse = User::firstOrCreate(
            ['username' => 'mgr.wh'],
            [
                'name' => 'Faisal Reza',
                'email' => 'warehouse@demo.com',
                'password' => $defaultPassword,
                'role' => 'mgr_ops',
                'job_title' => 'Warehouse Manager',
                'employment_type' => 'Full-Time',
                'division' => 'operasional',
                'parent' => $dirOps->username,
                'company_id' => $company->id,
                'is_active' => true,
                'account_status' => 'active',
                'base_salary' => 18000000,
            ]
        );

        $mgrAcc = User::firstOrCreate(
            ['username' => 'mgr.acc'],
            [
                'name' => 'Lisa Kumalasari',
                'email' => 'acc@demo.com',
                'password' => $defaultPassword,
                'role' => 'mgr_finance',
                'job_title' => 'Accounting Manager',
                'employment_type' => 'Full-Time',
                'division' => 'finance',
                'parent' => $dirFin->username,
                'company_id' => $company->id,
                'is_active' => true,
                'account_status' => 'active',
                'base_salary' => 20000000,
            ]
        );

        // 4. Supervisors
        $spvProd = User::firstOrCreate(
            ['username' => 'spv.prod'],
            [
                'name' => 'Eko Prasetyo',
                'email' => 'spv_prod@demo.com',
                'password' => $defaultPassword,
                'role' => 'staff_ops', // Supervisor is technically staff in role scope, or mgr depending on strictness
                'job_title' => 'Production Supervisor',
                'employment_type' => 'Full-Time',
                'division' => 'operasional',
                'parent' => $mgrProduction->username,
                'company_id' => $company->id,
                'is_active' => true,
                'account_status' => 'active',
                'base_salary' => 12000000,
            ]
        );

        $spvSales = User::firstOrCreate(
            ['username' => 'spv.sales'],
            [
                'name' => 'Hendra',
                'email' => 'spv_sales@demo.com',
                'password' => $defaultPassword,
                'role' => 'mgr_marketing',
                'job_title' => 'Sales Supervisor',
                'employment_type' => 'Full-Time',
                'division' => 'marketing',
                'parent' => $ceo->username, // Reports directly to CEO in this demo
                'company_id' => $company->id,
                'is_active' => true,
                'account_status' => 'active',
                'base_salary' => 15000000,
            ]
        );

        // 5. Staff
        for ($i = 1; $i <= 5; $i++) {
            User::firstOrCreate(
                ['username' => 'staff.prod.' . $i],
                [
                    'name' => 'Operator Produksi ' . $i,
                    'email' => 'operator'.$i.'@demo.com',
                    'password' => $defaultPassword,
                    'role' => 'staff_ops',
                    'job_title' => 'Machine Operator',
                    'employment_type' => 'Full-Time',
                    'division' => 'operasional',
                    'parent' => $spvProd->username,
                    'company_id' => $company->id,
                    'is_active' => true,
                    'account_status' => 'active',
                    'base_salary' => 5000000,
                ]
            );
        }

        for ($i = 1; $i <= 3; $i++) {
            User::firstOrCreate(
                ['username' => 'staff.wh.' . $i],
                [
                    'name' => 'Warehouse Staff ' . $i,
                    'email' => 'wh'.$i.'@demo.com',
                    'password' => $defaultPassword,
                    'role' => 'staff_ops',
                    'job_title' => 'Warehouse Staff',
                    'employment_type' => 'Contract',
                    'division' => 'operasional',
                    'parent' => $mgrWarehouse->username,
                    'company_id' => $company->id,
                    'is_active' => true,
                    'account_status' => 'active',
                    'base_salary' => 4500000,
                ]
            );
        }

        User::firstOrCreate(
            ['username' => 'staff.acc'],
            [
                'name' => 'Fitriani',
                'email' => 'staff_acc@demo.com',
                'password' => $defaultPassword,
                'role' => 'staff_finance',
                'job_title' => 'Junior Accountant',
                'employment_type' => 'Full-Time',
                'division' => 'finance',
                'parent' => $mgrAcc->username,
                'company_id' => $company->id,
                'is_active' => true,
                'account_status' => 'active',
                'base_salary' => 7000000,
            ]
        );

        // 6. Intern
        User::firstOrCreate(
            ['username' => 'intern.hr'],
            [
                'name' => 'Maya',
                'email' => 'intern_hr@demo.com',
                'password' => $defaultPassword,
                'role' => 'staff_hrd',
                'job_title' => 'HR Intern',
                'employment_type' => 'Intern',
                'division' => 'hrd',
                'parent' => $dirHR->username,
                'company_id' => $company->id,
                'is_active' => true,
                'account_status' => 'active',
                'base_salary' => 1500000,
            ]
        );
    }
}
