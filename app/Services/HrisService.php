<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Attendance;
use App\Models\Company;
use App\Models\HrAttendanceCorrection;
use App\Models\HrBranch;
use App\Models\HrCandidate;
use App\Models\HrDepartment;
use App\Models\HrDivision;
use App\Models\HrEmployeeCertification;
use App\Models\HrEmployeeContract;
use App\Models\HrEmployeeEducation;
use App\Models\HrEmployeeEmergencyContact;
use App\Models\HrEmployeeFamily;
use App\Models\HrEmployeeProfile;
use App\Models\HrEmployeeSkill;
use App\Models\HrExitClearance;
use App\Models\HrInterview;
use App\Models\HrJobGrade;
use App\Models\HrJobVacancy;
use App\Models\HrOkr;
use App\Models\HrOneOnOne;
use App\Models\HrPerformanceReview;
use App\Models\HrPosition;
use App\Models\HrPromotionRecommendation;
use App\Models\HrRoster;
use App\Models\LeaveQuota;
use App\Models\LeaveRequest;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Models\Payslip;
use App\Models\ResignationRequest;
use App\Models\Shift;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class HrisService
{
    // ==========================================
    // 1. ORGANIZATION MANAGEMENT
    // ==========================================

    public function createBranch(Company $company, array $data): HrBranch
    {
        return HrBranch::create(array_merge($data, ['company_id' => $company->id]));
    }

    public function createDepartment(Company $company, array $data): HrDepartment
    {
        return HrDepartment::create(array_merge($data, ['company_id' => $company->id]));
    }

    public function createDivision(Company $company, array $data): HrDivision
    {
        return HrDivision::create(array_merge($data, ['company_id' => $company->id]));
    }

    public function createJobGrade(Company $company, array $data): HrJobGrade
    {
        return HrJobGrade::create(array_merge($data, ['company_id' => $company->id]));
    }

    public function createPosition(Company $company, array $data): HrPosition
    {
        return HrPosition::create(array_merge($data, ['company_id' => $company->id]));
    }

    public function getOrganizationTree(Company $company): array
    {
        $departments = HrDepartment::where('company_id', $company->id)
            ->with(['manager', 'divisions.positions', 'children'])
            ->whereNull('parent_id')
            ->get();

        return $departments->toArray();
    }

    // ==========================================
    // 2. EMPLOYEE MASTER & PROFILE
    // ==========================================

    public function generateNik(Company $company): string
    {
        $prefix = 'EMP-'.date('Ym').'-';
        $count = User::where('company_id', $company->id)->count() + 1;

        return $prefix.str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    public function createEmployee(Company $company, array $userData, array $profileData = [], array $contractData = []): User
    {
        return DB::transaction(function () use ($company, $userData, $profileData, $contractData) {
            $user = User::create([
                'company_id' => $company->id,
                'name' => $userData['name'],
                'email' => $userData['email'],
                'username' => $userData['username'] ?? Str::slug($userData['name']).rand(100, 999),
                'password' => Hash::make($userData['password'] ?? 'password123'),
                'role' => $userData['role'] ?? 'staff',
                'job_title' => $userData['job_title'] ?? 'Staff Member',
                'division' => $userData['division'] ?? 'operasional',
                'branch_location' => $userData['branch_location'] ?? 'Head Office',
                'is_active' => true,
                'is_approved' => true,
                'base_salary' => $contractData['basic_salary'] ?? $userData['base_salary'] ?? 5000000,
            ]);

            $nik = $profileData['nik'] ?? $this->generateNik($company);

            HrEmployeeProfile::create(array_merge($profileData, [
                'user_id' => $user->id,
                'nik' => $nik,
            ]));

            if (! empty($contractData)) {
                HrEmployeeContract::create(array_merge($contractData, [
                    'user_id' => $user->id,
                    'contract_number' => $contractData['contract_number'] ?? 'CTR/'.date('Y').'/'.rand(1000, 9999),
                    'start_date' => $contractData['start_date'] ?? date('Y-m-d'),
                    'status' => 'active',
                ]));
            }

            // Create initial leave quota
            LeaveQuota::updateOrCreate(
                ['user_id' => $user->id, 'year' => (int) date('Y')],
                ['company_id' => $company->id, 'total_quota' => 12, 'used_quota' => 0]
            );

            return $user;
        });
    }

    public function updateEmployeeProfile(User $user, array $data): HrEmployeeProfile
    {
        $profile = HrEmployeeProfile::firstOrCreate(['user_id' => $user->id]);
        $profile->update($data);

        return $profile;
    }

    public function addFamilyMember(User $user, array $data): HrEmployeeFamily
    {
        return HrEmployeeFamily::create(array_merge($data, ['user_id' => $user->id]));
    }

    public function addEducation(User $user, array $data): HrEmployeeEducation
    {
        return HrEmployeeEducation::create(array_merge($data, ['user_id' => $user->id]));
    }

    public function addCertification(User $user, array $data): HrEmployeeCertification
    {
        return HrEmployeeCertification::create(array_merge($data, ['user_id' => $user->id]));
    }

    public function addSkill(User $user, array $data): HrEmployeeSkill
    {
        return HrEmployeeSkill::create(array_merge($data, ['user_id' => $user->id]));
    }

    public function addEmergencyContact(User $user, array $data): HrEmployeeEmergencyContact
    {
        return HrEmployeeEmergencyContact::create(array_merge($data, ['user_id' => $user->id]));
    }

    // ==========================================
    // 3. ATTENDANCE & ROSTER ENGINE
    // ==========================================

    public function assignRoster(Company $company, User $user, Shift $shift, string $date): HrRoster
    {
        return HrRoster::updateOrCreate(
            ['user_id' => $user->id, 'roster_date' => $date],
            ['company_id' => $company->id, 'shift_id' => $shift->id]
        );
    }

    public function processClockIn(User $user, string $clockInTime, ?string $lat = null, ?string $lng = null): Attendance
    {
        $today = date('Y-m-d');
        $roster = HrRoster::where('user_id', $user->id)->where('roster_date', $today)->first();
        $shift = $roster ? $roster->shift : Shift::first();

        $shiftStartTime = $shift ? $shift->start_time : '08:00:00';
        $tolerance = $shift ? ($shift->late_tolerance_minutes ?? 15) : 15;

        $scheduledStart = Carbon::parse($today.' '.$shiftStartTime);
        $actualClockIn = Carbon::parse($clockInTime);

        $lateMinutes = 0;
        if ($actualClockIn->gt($scheduledStart->copy()->addMinutes($tolerance))) {
            $lateMinutes = $actualClockIn->diffInMinutes($scheduledStart);
        }

        return Attendance::updateOrCreate(
            ['user_id' => $user->id, 'date' => $today],
            [
                'company_id' => $user->company_id,
                'clock_in' => $actualClockIn,
                'check_in' => $actualClockIn->format('H:i:s'),
                'status' => $lateMinutes > 0 ? 'late' : 'present',
                'shift_type' => $shift ? $shift->name : 'regular',
                'late_minutes' => $lateMinutes,
            ]
        );
    }

    public function processClockOut(User $user, string $clockOutTime): Attendance
    {
        $today = date('Y-m-d');
        $attendance = Attendance::where('user_id', $user->id)
            ->where(function ($q) use ($today) {
                $q->where('date', $today)->orWhereDate('clock_in', $today);
            })
            ->latest()
            ->first();

        if (! $attendance) {
            $attendance = Attendance::create([
                'user_id' => $user->id,
                'company_id' => $user->company_id,
                'date' => $today,
                'status' => 'present',
            ]);
        }

        $roster = HrRoster::where('user_id', $user->id)->where('roster_date', $today)->first();
        $shift = $roster ? $roster->shift : Shift::first();

        $shiftEndTime = $shift ? $shift->end_time : '17:00:00';
        $scheduledEnd = Carbon::parse($today.' '.$shiftEndTime);
        $actualClockOut = Carbon::parse($clockOutTime);

        $earlyLeaveMinutes = 0;
        if ($actualClockOut->lt($scheduledEnd)) {
            $earlyLeaveMinutes = $scheduledEnd->diffInMinutes($actualClockOut);
        }

        $overtimeHours = 0;
        if ($actualClockOut->gt($scheduledEnd->copy()->addHour())) {
            $overtimeHours = round($actualClockOut->diffInMinutes($scheduledEnd) / 60, 2);
        }

        $attendance->update([
            'clock_out' => $actualClockOut,
            'check_out' => $actualClockOut->format('H:i:s'),
            'early_leave_minutes' => $earlyLeaveMinutes,
            'overtime_hours' => $overtimeHours,
        ]);

        return $attendance;
    }

    public function submitAttendanceCorrection(User $user, array $data): HrAttendanceCorrection
    {
        return HrAttendanceCorrection::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'attendance_id' => $data['attendance_id'] ?? null,
            'date' => $data['date'],
            'corrected_check_in' => $data['corrected_check_in'] ?? null,
            'corrected_check_out' => $data['corrected_check_out'] ?? null,
            'reason' => $data['reason'],
            'status' => 'pending',
        ]);
    }

    public function approveAttendanceCorrection(HrAttendanceCorrection $correction, User $approver): bool
    {
        return DB::transaction(function () use ($correction, $approver) {
            $correction->update([
                'status' => 'approved',
                'approved_by' => $approver->id,
                'approved_at' => now(),
            ]);

            Attendance::updateOrCreate(
                ['user_id' => $correction->user_id, 'date' => $correction->date],
                [
                    'company_id' => $correction->company_id,
                    'check_in' => $correction->corrected_check_in ?? '08:00:00',
                    'check_out' => $correction->corrected_check_out ?? '17:00:00',
                    'status' => 'present',
                    'late_minutes' => 0,
                    'early_leave_minutes' => 0,
                ]
            );

            return true;
        });
    }

    // ==========================================
    // 4. LEAVE MANAGEMENT & BALANCE
    // ==========================================

    public function submitLeaveRequest(User $user, array $data): LeaveRequest
    {
        $startDate = Carbon::parse($data['start_date']);
        $endDate = Carbon::parse($data['end_date']);
        $days = $startDate->diffInDays($endDate) + 1;

        $leaveType = $data['leave_type'] ?? 'annual';

        return LeaveRequest::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'type' => $leaveType,
            'leave_type' => $leaveType, // annual, sick, permission, maternity
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'total_days' => $days,
            'reason' => $data['reason'],
            'status' => 'pending',
        ]);
    }

    public function approveLeaveRequest(LeaveRequest $request, User $approver): bool
    {
        return DB::transaction(function () use ($request, $approver) {
            $request->update([
                'status' => 'approved',
                'approved_by' => $approver->id,
            ]);

            // Deduct quota if annual leave
            $currentType = $request->leave_type ?? $request->type ?? 'annual';
            if (in_array($currentType, ['annual', 'permission'])) {
                $year = (int) Carbon::parse($request->start_date)->format('Y');
                $quota = LeaveQuota::firstOrCreate(
                    ['user_id' => $request->user_id, 'year' => $year],
                    ['company_id' => $request->company_id, 'total_quota' => 12, 'used_quota' => 0]
                );
                $daysToDeduct = $request->total_days ?: (Carbon::parse($request->start_date)->diffInDays(Carbon::parse($request->end_date)) + 1);
                $quota->increment('used_quota', $daysToDeduct);
            }

            return true;
        });
    }

    // ==========================================
    // 5. PAYROLL PREPARATION & CALCULATION ENGINE
    // ==========================================

    public function calculateAndGeneratePayroll(Company $company, string $monthYear): Collection
    {
        $users = User::where('company_id', $company->id)->where('is_active', true)->get();
        $payrolls = collect();

        DB::transaction(function () use ($company, $users, $monthYear, &$payrolls) {
            foreach ($users as $user) {
                $contract = HrEmployeeContract::where('user_id', $user->id)->where('status', 'active')->first();
                $baseSalary = $contract ? $contract->basic_salary : ($user->base_salary ?? 5000000);

                // Attendance sync: count overtime & late penalties for month
                $attendances = Attendance::where('user_id', $user->id)
                    ->where('date', 'like', Carbon::createFromFormat('m-Y', $monthYear)->format('Y-m').'%')
                    ->get();

                $totalOvertimeHours = $attendances->sum('overtime_hours');
                $totalLateMinutes = $attendances->sum('late_minutes');

                $overtimePay = $totalOvertimeHours * ($baseSalary / 173) * 1.5;
                $lateDeduction = ($totalLateMinutes / 60) * ($baseSalary / 173);

                // Additional fixed components
                $allowanceComponents = $user->hrSalaryComponents()
                    ->whereHas('salaryComponent', fn ($q) => $q->where('type', 'allowance'))
                    ->get();
                $otherAllowances = $allowanceComponents->sum('amount');

                $deductionComponents = $user->hrSalaryComponents()
                    ->whereHas('salaryComponent', fn ($q) => $q->where('type', 'deduction'))
                    ->get();
                $otherDeductions = $deductionComponents->sum('amount');

                // Standard statutory deductions (BPJS TK 2%, BPJS Kes 1%, PPh21 estimated 5%)
                $bpjsTk = $baseSalary * 0.02;
                $bpjsKes = $baseSalary * 0.01;
                $pph21 = max(0, ($baseSalary + $otherAllowances + $overtimePay - 4500000) * 0.05);

                $totalAllowances = $otherAllowances + $overtimePay;
                $totalDeductions = $otherDeductions + $lateDeduction + $bpjsTk + $bpjsKes + $pph21;
                $netSalary = max(0, $baseSalary + $totalAllowances - $totalDeductions);

                $dtPeriod = Carbon::createFromFormat('m-Y', $monthYear);
                $periodStart = $dtPeriod->copy()->startOfMonth()->format('Y-m-d');
                $periodEnd = $dtPeriod->copy()->endOfMonth()->format('Y-m-d');

                $payroll = Payroll::updateOrCreate(
                    ['user_id' => $user->id, 'month_year' => $monthYear],
                    [
                        'company_id' => $company->id,
                        'period_start' => $periodStart,
                        'period_end' => $periodEnd,
                        'base_salary' => $baseSalary,
                        'base_amount' => $baseSalary,
                        'allowances' => $totalAllowances,
                        'total_allowances' => $totalAllowances,
                        'deductions' => $totalDeductions,
                        'total_deductions' => $totalDeductions,
                        'net_salary' => $netSalary,
                        'net_amount' => $netSalary,
                        'status' => 'draft',
                    ]
                );

                // Populate payroll items
                PayrollItem::where('payroll_id', $payroll->id)->delete();
                PayrollItem::create(['payroll_id' => $payroll->id, 'name' => 'Overtime Pay', 'type' => 'allowance', 'amount' => $overtimePay]);
                PayrollItem::create(['payroll_id' => $payroll->id, 'name' => 'Fixed Allowances', 'type' => 'allowance', 'amount' => $otherAllowances]);
                PayrollItem::create(['payroll_id' => $payroll->id, 'name' => 'Late Deductions', 'type' => 'deduction', 'amount' => $lateDeduction]);
                PayrollItem::create(['payroll_id' => $payroll->id, 'name' => 'BPJS Ketenagakerjaan (2%)', 'type' => 'deduction', 'amount' => $bpjsTk]);
                PayrollItem::create(['payroll_id' => $payroll->id, 'name' => 'BPJS Kesehatan (1%)', 'type' => 'deduction', 'amount' => $bpjsKes]);
                PayrollItem::create(['payroll_id' => $payroll->id, 'name' => 'PPh21 Tax', 'type' => 'deduction', 'amount' => $pph21]);

                // Create Payslip record
                Payslip::updateOrCreate(
                    ['user_id' => $user->id, 'month_year' => $monthYear],
                    [
                        'company_id' => $company->id,
                        'base_salary' => $baseSalary,
                        'allowances' => $totalAllowances,
                        'deductions' => $totalDeductions,
                        'net_salary' => $netSalary,
                        'status' => 'published',
                        'notes' => "Payslip generated for $monthYear",
                    ]
                );

                $payrolls->push($payroll);
            }
        });

        return $payrolls;
    }

    public function approvePayroll(Payroll $payroll): bool
    {
        return $payroll->update(['status' => 'approved']);
    }

    // ==========================================
    // 6. PERFORMANCE & OKR MANAGEMENT
    // ==========================================

    public function createOkr(Company $company, User $user, array $data): HrOkr
    {
        return HrOkr::create(array_merge($data, [
            'company_id' => $company->id,
            'user_id' => $user->id,
        ]));
    }

    public function recordPerformanceReview(Company $company, User $user, User $reviewer, array $data): HrPerformanceReview
    {
        // Compute task completion score
        $totalTasks = Task::where('assigned_to', $user->id)->count();
        $completedTasks = Task::where('assigned_to', $user->id)->where('status', 'completed')->count();
        $taskScore = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 2) : 80;

        // Compute OKR score
        $okrs = HrOkr::where('user_id', $user->id)->get();
        $okrScore = $okrs->count() > 0
            ? round($okrs->avg(fn ($o) => ($o->current_value / max(1, $o->target_value)) * 100), 2)
            : 85;

        $kpiScore = $data['kpi_score'] ?? 85;
        $overall = round(($kpiScore * 0.4) + ($okrScore * 0.4) + ($taskScore * 0.2), 2);

        $grade = 'B';
        if ($overall >= 90) {
            $grade = 'A';
        } elseif ($overall >= 75) {
            $grade = 'B';
        } elseif ($overall >= 60) {
            $grade = 'C';
        } else {
            $grade = 'D';
        }

        return HrPerformanceReview::create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'reviewer_id' => $reviewer->id,
            'review_period' => $data['review_period'] ?? date('Y').'-Annual',
            'kpi_score' => $kpiScore,
            'okr_score' => $okrScore,
            'task_score' => $taskScore,
            'overall_score' => $overall,
            'grade' => $grade,
            'summary_notes' => $data['summary_notes'] ?? 'Performance evaluation completed.',
            'status' => 'completed',
        ]);
    }

    public function createOneOnOne(Company $company, User $manager, User $employee, array $data): HrOneOnOne
    {
        return HrOneOnOne::create(array_merge($data, [
            'company_id' => $company->id,
            'manager_id' => $manager->id,
            'employee_id' => $employee->id,
        ]));
    }

    public function submitPromotionRecommendation(Company $company, User $user, User $recommender, array $data): HrPromotionRecommendation
    {
        return HrPromotionRecommendation::create(array_merge($data, [
            'company_id' => $company->id,
            'user_id' => $user->id,
            'recommended_by' => $recommender->id,
            'status' => 'pending',
        ]));
    }

    // ==========================================
    // 7. RECRUITMENT & HIRING PIPELINE
    // ==========================================

    public function createJobVacancy(Company $company, array $data): HrJobVacancy
    {
        return HrJobVacancy::create(array_merge($data, ['company_id' => $company->id]));
    }

    public function applyCandidate(Company $company, HrJobVacancy $vacancy, array $data): HrCandidate
    {
        return HrCandidate::create(array_merge($data, [
            'company_id' => $company->id,
            'job_vacancy_id' => $vacancy->id,
            'status' => 'applied',
        ]));
    }

    public function scheduleInterview(HrCandidate $candidate, User $interviewer, string $scheduledAt): HrInterview
    {
        $candidate->update(['status' => 'interviewed']);

        return HrInterview::create([
            'candidate_id' => $candidate->id,
            'interviewer_id' => $interviewer->id,
            'scheduled_at' => $scheduledAt,
        ]);
    }

    public function hireCandidate(HrCandidate $candidate): User
    {
        return DB::transaction(function () use ($candidate) {
            $candidate->update(['status' => 'hired']);

            // 1-Click Hire: Convert candidate to Employee Master
            return $this->createEmployee(
                Company::find($candidate->company_id),
                [
                    'name' => $candidate->full_name,
                    'email' => $candidate->email,
                    'role' => 'staff',
                    'job_title' => $candidate->jobVacancy->title ?? 'New Hire',
                ],
                [
                    'nik' => $this->generateNik(Company::find($candidate->company_id)),
                ],
                [
                    'basic_salary' => 6000000,
                    'contract_type' => 'Probation',
                    'start_date' => date('Y-m-d'),
                ]
            );
        });
    }

    // ==========================================
    // 8. EMPLOYEE EXIT & ALUMNI CONVERSION
    // ==========================================

    public function processResignation(User $user, string $reason, string $effectiveDate): ResignationRequest
    {
        return DB::transaction(function () use ($user, $reason, $effectiveDate) {
            $resignation = ResignationRequest::create([
                'company_id' => $user->company_id,
                'user_id' => $user->id,
                'reason' => $reason,
                'effective_date' => $effectiveDate,
                'last_working_date' => $effectiveDate,
                'status' => 'approved',
            ]);

            // Create mandatory exit clearance checklist items
            $departments = ['IT (Laptop & Accounts)', 'Finance (Outstanding Loans)', 'HR (ID Card & Docs)', 'Operations (Keys & Access)'];
            foreach ($departments as $dept) {
                HrExitClearance::create([
                    'company_id' => $user->company_id,
                    'resignation_request_id' => $resignation->id,
                    'user_id' => $user->id,
                    'department_name' => explode(' ', $dept)[0],
                    'clearance_item' => $dept,
                    'is_cleared' => false,
                ]);
            }

            return $resignation;
        });
    }

    public function clearExitItem(HrExitClearance $clearance, User $clearedBy): bool
    {
        return DB::transaction(function () use ($clearance, $clearedBy) {
            $clearance->update([
                'is_cleared' => true,
                'cleared_by' => $clearedBy->id,
                'cleared_at' => now(),
            ]);

            // Check if all clearances are complete for this user
            $remaining = HrExitClearance::where('user_id', $clearance->user_id)
                ->where('is_cleared', false)
                ->count();

            if ($remaining === 0) {
                // Finalize exit: Transition user to Alumni
                $user = User::find($clearance->user_id);
                $user->update([
                    'is_active' => false,
                    'account_status' => 'alumni',
                    'former_role' => $user->role,
                    'alumni_since' => now(),
                    'resign_status' => 'completed',
                ]);
            }

            return true;
        });
    }
}
