<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\HrAttendanceCorrection;
use App\Models\HrCandidate;
use App\Models\HrDepartment;
use App\Models\HrDivision;
use App\Models\HrExitClearance;
use App\Models\HrJobGrade;
use App\Models\HrJobVacancy;
use App\Models\HrPosition;
use App\Models\LeaveRequest;
use App\Models\Payroll;
use App\Models\Shift;
use App\Models\User;
use App\Services\HrisService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HrisEnterpriseTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;
    protected User $adminUser;
    protected HrisService $hrisService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create(['name' => 'PT Test Enterprise', 'code' => 'TEST', 'slug' => 'pt-test-enterprise']);
        $this->adminUser = User::create([
            'company_id' => $this->company->id,
            'name' => 'HR Admin',
            'email' => 'hr.admin@test.com',
            'username' => 'hr.admin',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
            'is_approved' => true,
        ]);

        $this->hrisService = app(HrisService::class);
    }

    /** @test */
    public function test_01_organization_structure_crud()
    {
        $this->actingAs($this->adminUser);

        // 1. Create Department
        $deptResponse = $this->postJson('/api/hris-enterprise/departments', [
            'code' => 'ENG-TEST',
            'name' => 'Engineering Test',
        ]);
        $deptResponse->assertStatus(201);
        $this->assertDatabaseHas('hr_departments', ['code' => 'ENG-TEST']);

        $deptId = $deptResponse->json('data.id');

        // 2. Create Division
        $divResponse = $this->postJson('/api/hris-enterprise/divisions', [
            'department_id' => $deptId,
            'code' => 'DIV-DEV',
            'name' => 'Development Division',
        ]);
        $divResponse->assertStatus(201);

        // 3. Create Job Grade
        $gradeResponse = $this->postJson('/api/hris-enterprise/job-grades', [
            'code' => 'G1-TEST',
            'name' => 'Grade 1 Test',
            'level' => 1,
            'min_salary' => 5000000,
            'max_salary' => 10000000,
        ]);
        $gradeResponse->assertStatus(201);

        // 4. Create Position
        $posResponse = $this->postJson('/api/hris-enterprise/positions', [
            'division_id' => $divResponse->json('data.id'),
            'job_grade_id' => $gradeResponse->json('data.id'),
            'code' => 'POS-DEV-TEST',
            'title' => 'Software Engineer',
        ]);
        $posResponse->assertStatus(201);

        // 5. Fetch Org Tree
        $treeResponse = $this->getJson('/api/hris-enterprise/org-tree');
        $treeResponse->assertStatus(200);
    }

    /** @test */
    public function test_02_employee_master_and_profile_management()
    {
        $this->actingAs($this->adminUser);

        $empData = [
            'name' => 'Jane Doe',
            'email' => 'jane.doe@test.com',
            'username' => 'jane.doe',
            'job_title' => 'Backend Developer',
            'basic_salary' => 15000000,
            'contract_type' => 'PKWTT',
            'nik' => 'EMP-TEST-0001',
            'ktp_number' => '3171000000000001',
            'bank_name' => 'BCA',
            'bank_account_number' => '1234567890',
        ];

        $response = $this->postJson('/api/hris-enterprise/employees', $empData);
        $response->assertStatus(201);

        $this->assertDatabaseHas('users', ['email' => 'jane.doe@test.com']);
        $this->assertDatabaseHas('hr_employee_profiles', ['nik' => 'EMP-TEST-0001']);
        $this->assertDatabaseHas('hr_employee_contracts', ['contract_type' => 'PKWTT']);

        $user = User::where('email', 'jane.doe@test.com')->first();
        $this->hrisService->addEducation($user, ['degree' => 'Bachelor', 'institution' => 'UI', 'graduation_year' => 2020]);
        $this->assertDatabaseHas('hr_employee_educations', ['user_id' => $user->id, 'degree' => 'Bachelor']);
    }

    /** @test */
    public function test_03_attendance_and_correction_workflow()
    {
        $employee = $this->hrisService->createEmployee($this->company, [
            'name' => 'John Clock',
            'email' => 'john.clock@test.com',
        ]);

        $this->actingAs($employee);

        // Clock In
        $clockInResp = $this->postJson('/api/hris-enterprise/attendance/clock-in', [
            'clock_in_time' => '08:00:00',
        ]);
        $clockInResp->assertStatus(200);

        // Clock Out
        $clockOutResp = $this->postJson('/api/hris-enterprise/attendance/clock-out', [
            'clock_out_time' => '17:30:00',
        ]);
        $clockOutResp->assertStatus(200);

        // Submit Correction
        $corrResp = $this->postJson('/api/hris-enterprise/attendance/correction', [
            'date' => date('Y-m-d'),
            'corrected_check_in' => '07:55:00',
            'corrected_check_out' => '17:30:00',
            'reason' => 'Forgot badge scan',
        ]);
        $corrResp->assertStatus(201);

        $correctionId = $corrResp->json('data.id');
        $correction = HrAttendanceCorrection::find($correctionId);

        // Admin approves correction
        $this->actingAs($this->adminUser);
        $apprResp = $this->postJson("/api/hris-enterprise/attendance/correction/{$correction->id}/approve");
        $apprResp->assertStatus(200);

        $this->assertDatabaseHas('hr_attendance_corrections', ['id' => $correction->id, 'status' => 'approved']);
    }

    /** @test */
    public function test_04_leave_request_and_quota_balance()
    {
        $employee = $this->hrisService->createEmployee($this->company, [
            'name' => 'Alice Leave',
            'email' => 'alice.leave@test.com',
        ]);

        $this->actingAs($employee);

        $leaveResp = $this->postJson('/api/hris-enterprise/leave', [
            'leave_type' => 'annual',
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d', strtotime('+2 days')),
            'reason' => 'Family vacation',
        ]);
        $leaveResp->assertStatus(201);

        $leaveId = $leaveResp->json('data.id');
        $leaveRequest = LeaveRequest::find($leaveId);

        // Admin approves leave
        $this->actingAs($this->adminUser);
        $apprResp = $this->postJson("/api/hris-enterprise/leave/{$leaveRequest->id}/approve");
        $apprResp->assertStatus(200);

        $this->assertDatabaseHas('leave_requests', ['id' => $leaveRequest->id, 'status' => 'approved']);
        $this->assertDatabaseHas('leave_quotas', ['user_id' => $employee->id, 'used_quota' => 3]);
    }

    /** @test */
    public function test_05_automated_payroll_calculation()
    {
        $employee = $this->hrisService->createEmployee($this->company, [
            'name' => 'Bob Pay',
            'email' => 'bob.pay@test.com',
        ], [], ['basic_salary' => 10000000]);

        $this->actingAs($this->adminUser);

        $monthYear = date('m-Y');
        $payResp = $this->postJson('/api/hris-enterprise/payroll/generate', [
            'month_year' => $monthYear,
        ]);
        $payResp->assertStatus(200);

        $this->assertDatabaseHas('payrolls', ['user_id' => $employee->id, 'month_year' => $monthYear]);
        $this->assertDatabaseHas('payslips', ['user_id' => $employee->id, 'month_year' => $monthYear]);

        $payroll = Payroll::where('user_id', $employee->id)->where('month_year', $monthYear)->first();
        $apprResp = $this->postJson("/api/hris-enterprise/payroll/{$payroll->id}/approve");
        $apprResp->assertStatus(200);

        $this->assertDatabaseHas('payrolls', ['id' => $payroll->id, 'status' => 'approved']);
    }

    /** @test */
    public function test_06_performance_review_and_okrs()
    {
        $employee = $this->hrisService->createEmployee($this->company, [
            'name' => 'Charlie Performance',
            'email' => 'charlie.perf@test.com',
        ]);

        $this->actingAs($employee);

        $okrResp = $this->postJson('/api/hris-enterprise/okrs', [
            'objective' => 'Achieve 100% test coverage',
            'key_result' => 'Pass all PHPUnit tests',
            'target_value' => 100,
            'current_value' => 100,
        ]);
        $okrResp->assertStatus(201);

        $this->actingAs($this->adminUser);
        $revResp = $this->postJson("/api/hris-enterprise/performance-reviews/{$employee->id}", [
            'review_period' => '2026-Q3',
            'kpi_score' => 92,
            'summary_notes' => 'Excellent performance',
        ]);
        $revResp->assertStatus(201);

        $this->assertDatabaseHas('hr_performance_reviews', ['user_id' => $employee->id, 'grade' => 'A']);
    }

    /** @test */
    public function test_07_recruitment_pipeline_and_1click_hiring()
    {
        $this->actingAs($this->adminUser);

        // 1. Create Job Vacancy
        $vacResp = $this->postJson('/api/hris-enterprise/vacancies', [
            'title' => 'DevOps Specialist',
            'quota' => 1,
        ]);
        $vacResp->assertStatus(201);
        $vacancyId = $vacResp->json('data.id');

        // 2. Apply Candidate
        $candResp = $this->postJson("/api/hris-enterprise/vacancies/{$vacancyId}/apply", [
            'full_name' => 'David Candidate',
            'email' => 'david.cand@test.com',
            'phone' => '08123456789',
        ]);
        $candResp->assertStatus(201);
        $candidateId = $candResp->json('data.id');

        // 3. 1-Click Hire Candidate to Employee Master
        $hireResp = $this->postJson("/api/hris-enterprise/candidates/{$candidateId}/hire");
        $hireResp->assertStatus(200);

        $this->assertDatabaseHas('hr_candidates', ['id' => $candidateId, 'status' => 'hired']);
        $this->assertDatabaseHas('users', ['email' => 'david.cand@test.com']);
    }

    /** @test */
    public function test_08_employee_exit_and_clearance()
    {
        $employee = $this->hrisService->createEmployee($this->company, [
            'name' => 'Eve Exit',
            'email' => 'eve.exit@test.com',
        ]);

        $this->actingAs($employee);

        // Resignation
        $resigResp = $this->postJson('/api/hris-enterprise/exit/resignation', [
            'reason' => 'Relocating to another city',
            'effective_date' => date('Y-m-d', strtotime('+30 days')),
        ]);
        $resigResp->assertStatus(201);

        $this->assertDatabaseHas('resignation_requests', ['user_id' => $employee->id]);
        $this->assertDatabaseHas('hr_exit_clearances', ['user_id' => $employee->id]);

        // Approve clearances
        $clearances = HrExitClearance::where('user_id', $employee->id)->get();
        $this->actingAs($this->adminUser);

        foreach ($clearances as $c) {
            $clearResp = $this->postJson("/api/hris-enterprise/exit/clearance/{$c->id}/approve");
            $clearResp->assertStatus(200);
        }

        // Verify user account transitioned to alumni
        $this->assertDatabaseHas('users', ['id' => $employee->id, 'account_status' => 'alumni', 'is_active' => 0]);
    }
}
