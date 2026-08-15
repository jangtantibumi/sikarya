<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 0. Update Attendances Table with HRIS attributes
        Schema::table('attendances', function (Blueprint $table) {
            if (!Schema::hasColumn('attendances', 'date')) {
                $table->date('date')->nullable()->after('company_id');
            }
            if (!Schema::hasColumn('attendances', 'check_in')) {
                $table->string('check_in')->nullable();
            }
            if (!Schema::hasColumn('attendances', 'check_out')) {
                $table->string('check_out')->nullable();
            }
            if (!Schema::hasColumn('attendances', 'late_minutes')) {
                $table->integer('late_minutes')->default(0);
            }
            if (!Schema::hasColumn('attendances', 'early_leave_minutes')) {
                $table->integer('early_leave_minutes')->default(0);
            }
            if (!Schema::hasColumn('attendances', 'overtime_hours')) {
                $table->decimal('overtime_hours', 5, 2)->default(0);
            }
        });

        Schema::table('payrolls', function (Blueprint $table) {
            if (!Schema::hasColumn('payrolls', 'month_year')) {
                $table->string('month_year')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('payrolls', 'base_salary')) {
                $table->decimal('base_salary', 15, 2)->default(0);
            }
            if (!Schema::hasColumn('payrolls', 'allowances')) {
                $table->decimal('allowances', 15, 2)->default(0);
            }
            if (!Schema::hasColumn('payrolls', 'deductions')) {
                $table->decimal('deductions', 15, 2)->default(0);
            }
            if (!Schema::hasColumn('payrolls', 'net_salary')) {
                $table->decimal('net_salary', 15, 2)->default(0);
            }
        });

        Schema::table('leave_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('leave_requests', 'company_id')) {
                $table->foreignId('company_id')->nullable()->constrained()->cascadeOnDelete();
            }
            if (!Schema::hasColumn('leave_requests', 'leave_type')) {
                $table->string('leave_type')->nullable();
            }
            if (!Schema::hasColumn('leave_requests', 'total_days')) {
                $table->integer('total_days')->default(1);
            }
            if (!Schema::hasColumn('leave_requests', 'approved_by')) {
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            }
        });

        // 1. Organization Infrastructure
        Schema::create('hr_branches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->text('address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->integer('radius_meters')->default(100);
            $table->timestamps();

            $table->unique(['company_id', 'code']);
        });

        Schema::create('hr_departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('hr_departments')->nullOnDelete();
            $table->timestamps();

            $table->unique(['company_id', 'code']);
        });

        Schema::create('hr_divisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->constrained('hr_departments')->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->timestamps();

            $table->unique(['company_id', 'code']);
        });

        Schema::create('hr_job_grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->integer('level')->default(1);
            $table->decimal('min_salary', 15, 2)->default(0);
            $table->decimal('max_salary', 15, 2)->default(0);
            $table->timestamps();

            $table->unique(['company_id', 'code']);
        });

        Schema::create('hr_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('division_id')->nullable()->constrained('hr_divisions')->nullOnDelete();
            $table->foreignId('job_grade_id')->nullable()->constrained('hr_job_grades')->nullOnDelete();
            $table->string('code');
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'code']);
        });

        // 2. Employee Profile & History
        Schema::create('hr_employee_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('nik')->nullable()->unique();
            $table->string('ktp_number')->nullable();
            $table->string('birth_place')->nullable();
            $table->date('birth_date')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->string('marital_status')->default('single');
            $table->string('religion')->nullable();
            $table->text('address')->nullable();
            $table->string('npwp_number')->nullable();
            $table->string('bpjs_tk_number')->nullable();
            $table->string('bpjs_kes_number')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_account_holder')->nullable();
            $table->timestamps();
        });

        Schema::create('hr_employee_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('contract_number');
            $table->enum('contract_type', ['PKWT', 'PKWTT', 'Probation', 'Internship'])->default('PKWT');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->decimal('basic_salary', 15, 2)->default(0);
            $table->enum('status', ['active', 'expired', 'terminated'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('hr_employee_families', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('relationship'); // Spouse, Child, Parent
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->date('birth_date')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('is_dependent')->default(false);
            $table->timestamps();
        });

        Schema::create('hr_employee_educations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('degree'); // High School, Bachelor, Master, PhD
            $table->string('institution');
            $table->string('major')->nullable();
            $table->integer('graduation_year')->nullable();
            $table->decimal('gpa', 4, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('hr_employee_certifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('certificate_name');
            $table->string('issuing_organization');
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('certificate_file')->nullable();
            $table->timestamps();
        });

        Schema::create('hr_employee_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('skill_name');
            $table->string('proficiency_level')->default('Intermediate'); // Beginner, Intermediate, Expert
            $table->timestamps();
        });

        Schema::create('hr_employee_emergency_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('relationship');
            $table->string('phone');
            $table->text('address')->nullable();
            $table->timestamps();
        });

        Schema::create('hr_employee_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('document_type'); // KTP, NPWP, Diploma, Certificate, Contract
            $table->string('document_name');
            $table->string('file_path');
            $table->timestamps();
        });

        // 3. Attendance Roster & Correction
        Schema::create('hr_rosters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shift_id')->constrained('shifts')->cascadeOnDelete();
            $table->date('roster_date');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'roster_date']);
        });

        Schema::create('hr_attendance_corrections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attendance_id')->nullable()->constrained('attendances')->nullOnDelete();
            $table->date('date');
            $table->time('corrected_check_in')->nullable();
            $table->time('corrected_check_out')->nullable();
            $table->text('reason');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        // 4. Payroll Salary Components & Overtime Rules
        Schema::create('hr_salary_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->enum('type', ['allowance', 'deduction']);
            $table->enum('calculation_type', ['fixed', 'percentage', 'attendance_based'])->default('fixed');
            $table->boolean('is_taxable')->default(true);
            $table->decimal('default_amount', 15, 2)->default(0);
            $table->timestamps();

            $table->unique(['company_id', 'code']);
        });

        Schema::create('hr_employee_salary_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('salary_component_id')->constrained('hr_salary_components')->cascadeOnDelete();
            $table->decimal('amount', 15, 2)->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'salary_component_id']);
        });

        // 5. Performance, OKR, 1-on-1 & Promotion
        Schema::create('hr_okrs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('objective');
            $table->string('key_result');
            $table->decimal('target_value', 10, 2)->default(100);
            $table->decimal('current_value', 10, 2)->default(0);
            $table->decimal('weight', 5, 2)->default(1.00);
            $table->enum('status', ['draft', 'in_progress', 'completed', 'cancelled'])->default('in_progress');
            $table->timestamps();
        });

        Schema::create('hr_performance_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reviewer_id')->constrained('users')->cascadeOnDelete();
            $table->string('review_period'); // e.g. "2026-Q3", "2026-Annual"
            $table->decimal('kpi_score', 5, 2)->default(0);
            $table->decimal('okr_score', 5, 2)->default(0);
            $table->decimal('task_score', 5, 2)->default(0);
            $table->decimal('overall_score', 5, 2)->default(0);
            $table->string('grade')->default('B'); // A, B, C, D
            $table->text('summary_notes')->nullable();
            $table->enum('status', ['draft', 'submitted', 'completed'])->default('draft');
            $table->timestamps();
        });

        Schema::create('hr_one_on_ones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('manager_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->date('meeting_date');
            $table->text('notes');
            $table->text('action_items')->nullable();
            $table->timestamps();
        });

        Schema::create('hr_promotion_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recommended_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('target_position_id')->nullable()->constrained('hr_positions')->nullOnDelete();
            $table->foreignId('target_grade_id')->nullable()->constrained('hr_job_grades')->nullOnDelete();
            $table->text('justification');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 6. Recruitment Module
        Schema::create('hr_job_vacancies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->foreignId('department_id')->nullable()->constrained('hr_departments')->nullOnDelete();
            $table->foreignId('position_id')->nullable()->constrained('hr_positions')->nullOnDelete();
            $table->text('description')->nullable();
            $table->text('requirements')->nullable();
            $table->integer('quota')->default(1);
            $table->enum('status', ['open', 'closed', 'draft'])->default('open');
            $table->timestamps();
        });

        Schema::create('hr_candidates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_vacancy_id')->constrained('hr_job_vacancies')->cascadeOnDelete();
            $table->string('full_name');
            $table->string('email');
            $table->string('phone');
            $table->string('resume_path')->nullable();
            $table->enum('status', ['applied', 'shortlisted', 'interviewed', 'offered', 'hired', 'rejected'])->default('applied');
            $table->timestamps();
        });

        Schema::create('hr_interviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained('hr_candidates')->cascadeOnDelete();
            $table->foreignId('interviewer_id')->constrained('users')->cascadeOnDelete();
            $table->dateTime('scheduled_at');
            $table->integer('score')->nullable(); // 1-100
            $table->text('notes')->nullable();
            $table->enum('recommendation', ['hire', 'hold', 'reject'])->default('hold');
            $table->timestamps();
        });

        // 7. Employee Exit Clearances
        Schema::create('hr_exit_clearances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('resignation_request_id')->nullable()->constrained('resignation_requests')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('department_name'); // IT, Finance, HR, Operations
            $table->string('clearance_item');
            $table->boolean('is_cleared')->default(false);
            $table->foreignId('cleared_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cleared_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_exit_clearances');
        Schema::dropIfExists('hr_interviews');
        Schema::dropIfExists('hr_candidates');
        Schema::dropIfExists('hr_job_vacancies');
        Schema::dropIfExists('hr_promotion_recommendations');
        Schema::dropIfExists('hr_one_on_ones');
        Schema::dropIfExists('hr_performance_reviews');
        Schema::dropIfExists('hr_okrs');
        Schema::dropIfExists('hr_employee_salary_components');
        Schema::dropIfExists('hr_salary_components');
        Schema::dropIfExists('hr_attendance_corrections');
        Schema::dropIfExists('hr_rosters');
        Schema::dropIfExists('hr_employee_documents');
        Schema::dropIfExists('hr_employee_emergency_contacts');
        Schema::dropIfExists('hr_employee_skills');
        Schema::dropIfExists('hr_employee_certifications');
        Schema::dropIfExists('hr_employee_educations');
        Schema::dropIfExists('hr_employee_families');
        Schema::dropIfExists('hr_employee_contracts');
        Schema::dropIfExists('hr_employee_profiles');
        Schema::dropIfExists('hr_positions');
        Schema::dropIfExists('hr_job_grades');
        Schema::dropIfExists('hr_divisions');
        Schema::dropIfExists('hr_departments');
        Schema::dropIfExists('hr_branches');
    }
};
