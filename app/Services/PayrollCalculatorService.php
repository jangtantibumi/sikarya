<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Attendance;
use App\Models\HrEmployeeSalaryComponent;
use App\Models\HrSalaryComponent;
use App\Models\LeaveRequest;
use App\Models\Payroll;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PayrollCalculatorService
{
    public function generateMonthly(int $companyId, Carbon $periodStart, Carbon $periodEnd, int $creatorId): array
    {
        $users = User::where('company_id', $companyId)
            ->where('is_active', true)
            ->where('base_salary', '>', 0)
            ->get();

        $defaultComponents = HrSalaryComponent::where('company_id', $companyId)
            ->where('is_default', true)
            ->get();

        $generatedCount = 0;

        foreach ($users as $user) {
            DB::transaction(function () use ($user, $companyId, $periodStart, $periodEnd, $defaultComponents, &$generatedCount) {
                // Check if payroll already exists for this period
                $exists = Payroll::where('user_id', $user->id)
                    ->where('period_start', $periodStart)
                    ->where('period_end', $periodEnd)
                    ->exists();

                if ($exists) {
                    return;
                }

                $dailyRate = $user->base_salary / 22; // Assuming 22 working days
                $totalAllowances = 0;
                $totalDeductions = 0;
                $items = [];

                // 1. Calculate Overtime (Holiday Work)
                $overtimeDays = Attendance::where('user_id', $user->id)
                    ->whereBetween('clock_in', [$periodStart->copy()->startOfDay(), $periodEnd->copy()->endOfDay()])
                    ->where('is_holiday_work', true)
                    ->count();

                if ($overtimeDays > 0) {
                    $overtimeAllowance = $overtimeDays * $dailyRate * 1.5; // 1.5x daily rate for holiday
                    $totalAllowances += $overtimeAllowance;
                    $items[] = [
                        'type' => 'allowance',
                        'name' => 'Holiday Overtime ('.$overtimeDays.' days)',
                        'amount' => $overtimeAllowance,
                    ];
                }

                // 2. Calculate Unpaid Leave
                $unpaidLeaves = LeaveRequest::where('user_id', $user->id)
                    ->where('status', 'approved')
                    ->where('type', 'unpaid')
                    ->whereBetween('start_date', [$periodStart, $periodEnd])
                    ->get();

                $unpaidDays = 0;
                foreach ($unpaidLeaves as $leave) {
                    $start = Carbon::parse($leave->start_date)->max($periodStart);
                    $end = Carbon::parse($leave->end_date)->min($periodEnd);
                    $unpaidDays += $start->diffInDays($end) + 1;
                }

                if ($unpaidDays > 0) {
                    $unpaidDeduction = $unpaidDays * $dailyRate;
                    $totalDeductions += $unpaidDeduction;
                    $items[] = [
                        'type' => 'deduction',
                        'name' => 'Unpaid Leave ('.$unpaidDays.' days)',
                        'amount' => $unpaidDeduction,
                    ];
                }

                // 3. Process Salary Components (Defaults and User-Specific)
                $userComponents = HrEmployeeSalaryComponent::where('user_id', $user->id)->get()->keyBy('salary_component_id');
                $processedComponentIds = [];

                $allComponents = $defaultComponents->merge(
                    $userComponents->map(function ($uc) {
                        return $uc->salaryComponent;
                    })->filter()
                )->unique('id');

                foreach ($allComponents as $comp) {
                    if (in_array($comp->id, $processedComponentIds)) {
                        continue;
                    }
                    $processedComponentIds[] = $comp->id;

                    $amount = $comp->default_amount;
                    if ($userComponents->has($comp->id)) {
                        $amount = $userComponents->get($comp->id)->amount;
                    }

                    // Auto calculations for BPJS/PPH21 based on code
                    $code = strtoupper($comp->code);
                    if ($code === 'BPJS-KES') {
                        $amount = $user->base_salary * 0.01; // 1%
                    } elseif ($code === 'BPJS-TK') {
                        $amount = $user->base_salary * 0.02; // 2%
                    } elseif ($code === 'PPH21') {
                        // Rata-rata 5% dari (Gaji Pokok + Tunjangan)
                        $amount = ($user->base_salary + $totalAllowances) * 0.05;
                    }

                    if ($amount > 0) {
                        if ($comp->type === 'allowance') {
                            $totalAllowances += $amount;
                        } else {
                            $totalDeductions += $amount;
                        }
                        $items[] = [
                            'type' => $comp->type,
                            'name' => $comp->name,
                            'amount' => $amount,
                        ];
                    }
                }

                // Create Payroll
                $payroll = Payroll::create([
                    'company_id' => $companyId,
                    'user_id' => $user->id,
                    'period_start' => $periodStart,
                    'period_end' => $periodEnd,
                    'base_amount' => $user->base_salary,
                    'total_allowances' => $totalAllowances,
                    'total_deductions' => $totalDeductions,
                    'net_amount' => $user->base_salary + $totalAllowances - $totalDeductions,
                    'status' => 'draft',
                ]);

                // Create Items
                foreach ($items as $item) {
                    $payroll->items()->create([
                        'type' => $item['type'],
                        'description' => $item['name'], // Note: the migration uses 'description'
                        'amount' => $item['amount'],
                    ]);
                }

                $generatedCount++;
            });
        }

        return ['count' => $generatedCount];
    }
}
