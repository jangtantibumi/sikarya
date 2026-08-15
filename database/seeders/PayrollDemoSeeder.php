<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Company;
use App\Models\LeaveRequest;
use App\Models\Payroll;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PayrollDemoSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();
        if (!$company) {
            $company = Company::create([
                'name' => 'Demo Enterprise',
                'slug' => 'demo-enterprise'
            ]);
        }
        
        // 1. Create 10 Employees
        $employees = [];
        $salaries = [4500000, 5000000, 7500000, 12000000, 8000000, 6000000, 5500000, 4800000, 9000000, 15000000];
        $titles = ['Staff', 'Officer', 'Supervisor', 'Manager', 'Lead', 'Senior Staff', 'Staff', 'Staff', 'Specialist', 'VP'];
        
        for ($i = 0; $i < 10; $i++) {
            $employees[] = User::firstOrCreate(
                ['email' => "emp{$i}@demo.com"],
                [
                    'name' => "Demo Employee " . ($i + 1),
                    'username' => "emp{$i}",
                    'password' => Hash::make('password'),
                    'role' => 'employee',
                    'company_id' => $company->id,
                    'is_active' => true,
                    'base_salary' => $salaries[$i],
                    'job_title' => $titles[$i]
                ]
            );
        }

        // Period Setup
        $lastMonthStart = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();

        // 2. Add Attendance (Holiday overtime)
        // Employee 0 worked 2 holidays
        Attendance::firstOrCreate([
            'user_id' => $employees[0]->id,
            'clock_in' => $lastMonthStart->copy()->addDays(5)->setHour(8), // Assumption: holiday
        ], [
            'clock_out' => $lastMonthStart->copy()->addDays(5)->setHour(17),
            'is_holiday_work' => true,
            'status' => 'present'
        ]);
        Attendance::firstOrCreate([
            'user_id' => $employees[0]->id,
            'clock_in' => $lastMonthStart->copy()->addDays(12)->setHour(8),
        ], [
            'clock_out' => $lastMonthStart->copy()->addDays(12)->setHour(17),
            'is_holiday_work' => true,
            'status' => 'present'
        ]);

        // 3. Add Leave Request (Unpaid)
        // Employee 1 took 3 days unpaid leave
        LeaveRequest::firstOrCreate([
            'user_id' => $employees[1]->id,
            'start_date' => $lastMonthStart->copy()->addDays(10),
            'end_date' => $lastMonthStart->copy()->addDays(12),
        ], [
            'company_id' => $company->id,
            'type' => 'unpaid',
            'status' => 'approved',
            'reason' => 'Family matter'
        ]);

        // 4. Draft Payroll for Employee 2
        Payroll::firstOrCreate([
            'user_id' => $employees[2]->id,
            'period_start' => $lastMonthStart,
            'period_end' => $lastMonthEnd,
        ], [
            'company_id' => $company->id,
            'base_amount' => $employees[2]->base_salary,
            'net_amount' => $employees[2]->base_salary,
            'status' => 'draft'
        ]);

        // 5. Approved Payroll for Employee 3
        Payroll::firstOrCreate([
            'user_id' => $employees[3]->id,
            'period_start' => $lastMonthStart,
            'period_end' => $lastMonthEnd,
        ], [
            'company_id' => $company->id,
            'base_amount' => $employees[3]->base_salary,
            'net_amount' => $employees[3]->base_salary,
            'status' => 'approved',
            'approved_by' => User::where('role', 'ceo')->first()->id ?? 1
        ]);
    }
}
