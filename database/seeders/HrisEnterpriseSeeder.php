<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\HrBranch;
use App\Models\HrDepartment;
use App\Models\HrDivision;
use App\Models\HrJobGrade;
use App\Models\HrPosition;
use App\Models\HrSalaryComponent;
use App\Models\Shift;
use App\Models\User;
use App\Services\HrisService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class HrisEnterpriseSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::firstOrCreate(
            ['id' => 1],
            ['name' => 'PT Suba Enterprise', 'code' => 'SUBA']
        );

        $hrisService = app(HrisService::class);

        // 1. Branches
        $headOffice = HrBranch::firstOrCreate(
            ['company_id' => $company->id, 'code' => 'HO-JKT'],
            ['name' => 'Head Office Jakarta', 'address' => 'Jl. Sudirman No. 45, Jakarta Pusat', 'latitude' => -6.2088, 'longitude' => 106.8456, 'radius_meters' => 200]
        );

        $surabayaBranch = HrBranch::firstOrCreate(
            ['company_id' => $company->id, 'code' => 'BR-SBY'],
            ['name' => 'Surabaya Branch', 'address' => 'Jl. Pemuda No. 12, Surabaya', 'latitude' => -7.2575, 'longitude' => 112.7521, 'radius_meters' => 200]
        );

        // 2. Departments
        $deptEng = HrDepartment::firstOrCreate(
            ['company_id' => $company->id, 'code' => 'ENG'],
            ['name' => 'Engineering & IT']
        );

        $deptHr = HrDepartment::firstOrCreate(
            ['company_id' => $company->id, 'code' => 'HRD'],
            ['name' => 'Human Resources']
        );

        $deptFin = HrDepartment::firstOrCreate(
            ['company_id' => $company->id, 'code' => 'FIN'],
            ['name' => 'Finance & Accounting']
        );

        // 3. Divisions
        $divSoftware = HrDivision::firstOrCreate(
            ['company_id' => $company->id, 'code' => 'SW-ENG'],
            ['department_id' => $deptEng->id, 'name' => 'Software Engineering']
        );

        $divTalent = HrDivision::firstOrCreate(
            ['company_id' => $company->id, 'code' => 'TALENT'],
            ['department_id' => $deptHr->id, 'name' => 'Talent Management']
        );

        // 4. Job Grades
        $gradeJunior = HrJobGrade::firstOrCreate(
            ['company_id' => $company->id, 'code' => 'G1'],
            ['name' => 'Junior Staff', 'level' => 1, 'min_salary' => 5000000, 'max_salary' => 8000000]
        );

        $gradeSenior = HrJobGrade::firstOrCreate(
            ['company_id' => $company->id, 'code' => 'G3'],
            ['name' => 'Senior Specialist', 'level' => 3, 'min_salary' => 10000000, 'max_salary' => 18000000]
        );

        $gradeManager = HrJobGrade::firstOrCreate(
            ['company_id' => $company->id, 'code' => 'G4'],
            ['name' => 'Department Manager', 'level' => 4, 'min_salary' => 20000000, 'max_salary' => 35000000]
        );

        // 5. Positions
        $posLeadEng = HrPosition::firstOrCreate(
            ['company_id' => $company->id, 'code' => 'POS-ENG-LEAD'],
            ['division_id' => $divSoftware->id, 'job_grade_id' => $gradeManager->id, 'title' => 'Engineering Lead', 'description' => 'Leads software development team']
        );

        $posDev = HrPosition::firstOrCreate(
            ['company_id' => $company->id, 'code' => 'POS-DEV'],
            ['division_id' => $divSoftware->id, 'job_grade_id' => $gradeSenior->id, 'title' => 'Senior Fullstack Engineer', 'description' => 'Develops core ERP modules']
        );

        $posHr = HrPosition::firstOrCreate(
            ['company_id' => $company->id, 'code' => 'POS-HR-SPEC'],
            ['division_id' => $divTalent->id, 'job_grade_id' => $gradeJunior->id, 'title' => 'HR Generalist', 'description' => 'Handles employee relations and payroll']
        );

        // 6. Salary Components
        $compTransport = HrSalaryComponent::firstOrCreate(
            ['company_id' => $company->id, 'code' => 'ALLOW-TRANS'],
            ['name' => 'Transport Allowance', 'type' => 'allowance', 'calculation_type' => 'fixed', 'is_taxable' => true, 'default_amount' => 1000000]
        );

        $compMeal = HrSalaryComponent::firstOrCreate(
            ['company_id' => $company->id, 'code' => 'ALLOW-MEAL'],
            ['name' => 'Meal Allowance', 'type' => 'allowance', 'calculation_type' => 'attendance_based', 'is_taxable' => true, 'default_amount' => 500000]
        );

        // 7. Shift Master
        $morningShift = Shift::firstOrCreate(
            ['company_id' => $company->id, 'name' => 'Morning Shift'],
            ['start_time' => '08:00:00', 'end_time' => '17:00:00']
        );

        // 8. Create Demo Employees with complete data
        $managerUser = User::where('email', 'budi.santoso@subaerp.com')->first() ?: $hrisService->createEmployee(
            $company,
            [
                'name' => 'Budi Santoso',
                'email' => 'budi.santoso@subaerp.com',
                'username' => 'budi.santoso',
                'role' => 'manager',
                'job_title' => $posLeadEng->title,
                'division' => 'operasional',
            ],
            [
                'nik' => 'EMP-2026-0001',
                'ktp_number' => '3171012345670001',
                'birth_place' => 'Jakarta',
                'birth_date' => '1988-05-15',
                'gender' => 'male',
                'marital_status' => 'married',
                'religion' => 'Islam',
                'address' => 'Jl. Kebon Jeruk No. 10, Jakarta',
                'npwp_number' => '12.345.678.9-012.000',
                'bpjs_tk_number' => '98765432100',
                'bpjs_kes_number' => '12345678900',
                'bank_name' => 'Bank Mandiri',
                'bank_account_number' => '1230009876543',
                'bank_account_holder' => 'Budi Santoso',
            ],
            [
                'contract_number' => 'CTR/2026/001',
                'contract_type' => 'PKWTT',
                'start_date' => '2024-01-01',
                'basic_salary' => 25000000,
            ]
        );

        $staffUser = User::where('email', 'siti.rahma@subaerp.com')->first() ?: $hrisService->createEmployee(
            $company,
            [
                'name' => 'Siti Rahma',
                'email' => 'siti.rahma@subaerp.com',
                'username' => 'siti.rahma',
                'role' => 'staff',
                'job_title' => $posDev->title,
                'division' => 'operasional',
            ],
            [
                'nik' => 'EMP-2026-0002',
                'ktp_number' => '3171012345670002',
                'birth_place' => 'Bandung',
                'birth_date' => '1995-08-20',
                'gender' => 'female',
                'marital_status' => 'single',
                'religion' => 'Islam',
                'address' => 'Jl. Dago No. 88, Bandung',
                'npwp_number' => '98.765.432.1-012.000',
                'bpjs_tk_number' => '87654321099',
                'bpjs_kes_number' => '23456789011',
                'bank_name' => 'BCA',
                'bank_account_number' => '8880123456',
                'bank_account_holder' => 'Siti Rahma',
            ],
            [
                'contract_number' => 'CTR/2026/002',
                'contract_type' => 'PKWT',
                'start_date' => '2025-06-01',
                'basic_salary' => 12000000,
            ]
        );

        // Sub-details for Employee
        $hrisService->addFamilyMember($managerUser, ['name' => 'Dewi Santoso', 'relationship' => 'Spouse', 'gender' => 'female', 'is_dependent' => true]);
        $hrisService->addEducation($managerUser, ['degree' => 'Bachelor', 'institution' => 'Institut Teknologi Bandung', 'major' => 'Teknik Informatika', 'graduation_year' => 2010, 'gpa' => 3.85]);
        $hrisService->addCertification($managerUser, ['certificate_name' => 'PMP Certified Project Manager', 'issuing_organization' => 'PMI', 'issue_date' => '2021-03-10']);
        $hrisService->addSkill($managerUser, ['skill_name' => 'Laravel & PHP Enterprise', 'proficiency_level' => 'Expert']);
        $hrisService->addEmergencyContact($managerUser, ['name' => 'Dewi Santoso', 'relationship' => 'Wife', 'phone' => '081299887766']);

        // 9. Attendance, Roster & Payroll Execution
        $hrisService->assignRoster($company, $staffUser, $morningShift, date('Y-m-d'));
        $hrisService->processClockIn($staffUser, '08:05:00');
        $hrisService->processClockOut($staffUser, '18:30:00');

        // Generate Payroll for current month
        $monthYear = date('m-Y');
        $hrisService->calculateAndGeneratePayroll($company, $monthYear);

        // 10. Performance, OKR & Recruitment Setup
        $hrisService->createOkr($company, $staffUser, [
            'objective' => 'Implement Full Enterprise HRIS Module',
            'key_result' => 'Deliver 8 HRIS sub-modules with 100% test pass rate',
            'target_value' => 100,
            'current_value' => 100,
        ]);

        $hrisService->recordPerformanceReview($company, $staffUser, $managerUser, [
            'review_period' => '2026-Q3',
            'kpi_score' => 95,
            'summary_notes' => 'Outstanding execution on ERP HRIS module implementation.',
        ]);

        $vacancy = $hrisService->createJobVacancy($company, [
            'title' => 'Senior Frontend Engineer',
            'department_id' => $deptEng->id,
            'position_id' => $posDev->id,
            'description' => 'Building modern iOS-style ERP dashboards with Tailwind & Blade',
            'quota' => 2,
        ]);

        $candidate = $hrisService->applyCandidate($company, $vacancy, [
            'full_name' => 'Andi Wijaya',
            'email' => 'andi.wijaya@example.com',
            'phone' => '081344556677',
        ]);

        $hrisService->scheduleInterview($candidate, $managerUser, date('Y-m-d H:i:s', strtotime('+1 day')));
    }
}
