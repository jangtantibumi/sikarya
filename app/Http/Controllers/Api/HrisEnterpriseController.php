<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\HrAttendanceCorrection;
use App\Models\HrCandidate;
use App\Models\HrDepartment;
use App\Models\HrExitClearance;
use App\Models\HrJobGrade;
use App\Models\HrJobVacancy;
use App\Models\HrPosition;
use App\Models\LeaveRequest;
use App\Models\Payroll;
use App\Models\User;
use App\Services\HrisService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HrisEnterpriseController extends Controller
{
    protected HrisService $hrisService;

    public function __construct(HrisService $hrisService)
    {
        $this->hrisService = $hrisService;
    }

    protected function getCompany(Request $request): Company
    {
        return $request->user()->company ?? Company::first();
    }

    // ==========================================
    // 1. ORGANIZATION
    // ==========================================

    public function getOrgTree(Request $request): JsonResponse
    {
        $tree = $this->hrisService->getOrganizationTree($this->getCompany($request));
        return response()->json(['status' => 'success', 'data' => $tree]);
    }

    public function storeDepartment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string',
            'name' => 'required|string',
            'manager_id' => 'nullable|exists:users,id',
            'parent_id' => 'nullable|exists:hr_departments,id',
        ]);

        $dept = $this->hrisService->createDepartment($this->getCompany($request), $validated);
        return response()->json(['status' => 'success', 'message' => 'Department created successfully', 'data' => $dept], 201);
    }

    public function storeDivision(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'department_id' => 'required|exists:hr_departments,id',
            'code' => 'required|string',
            'name' => 'required|string',
        ]);

        $div = $this->hrisService->createDivision($this->getCompany($request), $validated);
        return response()->json(['status' => 'success', 'message' => 'Division created successfully', 'data' => $div], 201);
    }

    public function storePosition(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'division_id' => 'nullable|exists:hr_divisions,id',
            'job_grade_id' => 'nullable|exists:hr_job_grades,id',
            'code' => 'required|string',
            'title' => 'required|string',
            'description' => 'nullable|string',
        ]);

        $pos = $this->hrisService->createPosition($this->getCompany($request), $validated);
        return response()->json(['status' => 'success', 'message' => 'Position created successfully', 'data' => $pos], 201);
    }

    public function storeJobGrade(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string',
            'name' => 'required|string',
            'level' => 'required|integer',
            'min_salary' => 'required|numeric',
            'max_salary' => 'required|numeric',
        ]);

        $grade = $this->hrisService->createJobGrade($this->getCompany($request), $validated);
        return response()->json(['status' => 'success', 'message' => 'Job Grade created successfully', 'data' => $grade], 201);
    }

    // ==========================================
    // 2. EMPLOYEE MASTER & PROFILES
    // ==========================================

    public function getEmployees(Request $request): JsonResponse
    {
        $company = $this->getCompany($request);
        $employees = User::where('company_id', $company->id)
            ->with(['hrProfile', 'hrContracts', 'hrFamilies', 'hrEducations', 'hrCertifications', 'hrSkills', 'hrEmergencyContacts'])
            ->get();

        return response()->json(['status' => 'success', 'data' => $employees]);
    }

    public function storeEmployee(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'username' => 'nullable|string|unique:users,username',
            'role' => 'nullable|string',
            'job_title' => 'nullable|string',
            'basic_salary' => 'nullable|numeric',
            'contract_type' => 'nullable|string',
            'start_date' => 'nullable|date',
            'nik' => 'nullable|string',
            'ktp_number' => 'nullable|string',
            'birth_place' => 'nullable|string',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|string',
            'bank_name' => 'nullable|string',
            'bank_account_number' => 'nullable|string',
        ]);

        $employee = $this->hrisService->createEmployee(
            $this->getCompany($request),
            $request->only(['name', 'email', 'username', 'role', 'job_title', 'division', 'branch_location']),
            $request->only(['nik', 'ktp_number', 'birth_place', 'birth_date', 'gender', 'marital_status', 'religion', 'address', 'npwp_number', 'bpjs_tk_number', 'bpjs_kes_number', 'bank_name', 'bank_account_number', 'bank_account_holder']),
            $request->only(['contract_number', 'contract_type', 'start_date', 'end_date', 'basic_salary'])
        );

        return response()->json(['status' => 'success', 'message' => 'Employee onboarded successfully', 'data' => $employee], 201);
    }

    // ==========================================
    // 3. ATTENDANCE & CORRECTIONS
    // ==========================================

    public function clockIn(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'clock_in_time' => 'required|date_format:H:i:s',
        ]);

        $attendance = $this->hrisService->processClockIn($request->user(), $validated['clock_in_time']);
        return response()->json(['status' => 'success', 'message' => 'Clock In recorded successfully', 'data' => $attendance]);
    }

    public function clockOut(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'clock_out_time' => 'required|date_format:H:i:s',
        ]);

        $attendance = $this->hrisService->processClockOut($request->user(), $validated['clock_out_time']);
        return response()->json(['status' => 'success', 'message' => 'Clock Out recorded successfully', 'data' => $attendance]);
    }

    public function submitCorrection(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'attendance_id' => 'nullable|exists:attendances,id',
            'date' => 'required|date',
            'corrected_check_in' => 'nullable|date_format:H:i:s',
            'corrected_check_out' => 'nullable|date_format:H:i:s',
            'reason' => 'required|string',
        ]);

        $correction = $this->hrisService->submitAttendanceCorrection($request->user(), $validated);
        return response()->json(['status' => 'success', 'message' => 'Attendance correction submitted', 'data' => $correction], 201);
    }

    public function approveCorrection(Request $request, HrAttendanceCorrection $correction): JsonResponse
    {
        $this->hrisService->approveAttendanceCorrection($correction, $request->user());
        return response()->json(['status' => 'success', 'message' => 'Attendance correction approved']);
    }

    // ==========================================
    // 4. LEAVE
    // ==========================================

    public function submitLeave(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'leave_type' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string',
        ]);

        $leave = $this->hrisService->submitLeaveRequest($request->user(), $validated);
        return response()->json(['status' => 'success', 'message' => 'Leave request submitted', 'data' => $leave], 201);
    }

    public function approveLeave(Request $request, LeaveRequest $leaveRequest): JsonResponse
    {
        $this->hrisService->approveLeaveRequest($leaveRequest, $request->user());
        return response()->json(['status' => 'success', 'message' => 'Leave request approved']);
    }

    // ==========================================
    // 5. PAYROLL PREPARATION
    // ==========================================

    public function generatePayroll(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'month_year' => 'required|string', // e.g. "08-2026"
        ]);

        $payrolls = $this->hrisService->calculateAndGeneratePayroll($this->getCompany($request), $validated['month_year']);
        return response()->json(['status' => 'success', 'message' => 'Payroll generated successfully for ' . $validated['month_year'], 'data' => $payrolls]);
    }

    public function approvePayroll(Request $request, Payroll $payroll): JsonResponse
    {
        $this->hrisService->approvePayroll($payroll);
        return response()->json(['status' => 'success', 'message' => 'Payroll approved']);
    }

    // ==========================================
    // 6. PERFORMANCE & OKR
    // ==========================================

    public function storeOkr(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'objective' => 'required|string',
            'key_result' => 'required|string',
            'target_value' => 'required|numeric',
            'current_value' => 'nullable|numeric',
        ]);

        $okr = $this->hrisService->createOkr($this->getCompany($request), $request->user(), $validated);
        return response()->json(['status' => 'success', 'message' => 'OKR created successfully', 'data' => $okr], 201);
    }

    public function storePerformanceReview(Request $request, User $employee): JsonResponse
    {
        $validated = $request->validate([
            'review_period' => 'required|string',
            'kpi_score' => 'required|numeric',
            'summary_notes' => 'nullable|string',
        ]);

        $review = $this->hrisService->recordPerformanceReview($this->getCompany($request), $employee, $request->user(), $validated);
        return response()->json(['status' => 'success', 'message' => 'Performance review recorded', 'data' => $review], 201);
    }

    // ==========================================
    // 7. RECRUITMENT & HIRING
    // ==========================================

    public function storeJobVacancy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'department_id' => 'nullable|exists:hr_departments,id',
            'position_id' => 'nullable|exists:hr_positions,id',
            'description' => 'nullable|string',
            'quota' => 'required|integer',
        ]);

        $vacancy = $this->hrisService->createJobVacancy($this->getCompany($request), $validated);
        return response()->json(['status' => 'success', 'message' => 'Job vacancy created successfully', 'data' => $vacancy], 201);
    }

    public function applyCandidate(Request $request, HrJobVacancy $vacancy): JsonResponse
    {
        $validated = $request->validate([
            'full_name' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required|string',
        ]);

        $candidate = $this->hrisService->applyCandidate($this->getCompany($request), $vacancy, $validated);
        return response()->json(['status' => 'success', 'message' => 'Application submitted successfully', 'data' => $candidate], 201);
    }

    public function hireCandidate(Request $request, HrCandidate $candidate): JsonResponse
    {
        $employee = $this->hrisService->hireCandidate($candidate);
        return response()->json(['status' => 'success', 'message' => 'Candidate hired & converted to Employee Master', 'data' => $employee]);
    }

    // ==========================================
    // 8. EMPLOYEE EXIT & CLEARANCE
    // ==========================================

    public function processResignation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string',
            'effective_date' => 'required|date',
        ]);

        $resignation = $this->hrisService->processResignation($request->user(), $validated['reason'], $validated['effective_date']);
        return response()->json(['status' => 'success', 'message' => 'Resignation & Exit Clearance initiated', 'data' => $resignation], 201);
    }

    public function approveClearance(Request $request, HrExitClearance $clearance): JsonResponse
    {
        $this->hrisService->clearExitItem($clearance, $request->user());
        return response()->json(['status' => 'success', 'message' => 'Exit Clearance item cleared']);
    }
}
