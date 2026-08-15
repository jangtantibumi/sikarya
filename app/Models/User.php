<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'username',
        'employee_code',
        'role',
        'job_title',
        'division',
        'is_active',
        'account_status',
        'former_role',
        'former_parent',
        'alumni_since',
        'deactivated_at',
        'archived_at',
        'anonymized_at',
        'legal_hold',
        'signature_image_path',
        'signature_consented_at',
        'parent',
        'otp_code',
        'otp_expires_at',
        'otp_attempts',
        'otp_locked_until',
        'otp_last_sent_at',
        'employment_type',
        'company_id',
        'branch_location',
        'bio',
        'profile_picture_path',
        'reports_to_id',
        'default_shift_id',
        'is_approved',
        'base_salary',
        'default_leave_quota',
        'target_hours_per_month',
        'resign_status',
        'resign_reason',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'otp_code',
        'gemini_api_key',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'otp_expires_at' => 'datetime',
            'otp_locked_until' => 'datetime',
            'otp_last_sent_at' => 'datetime',
            'otp_attempts' => 'integer',
            'is_active' => 'boolean',
            'alumni_since' => 'datetime',
            'deactivated_at' => 'datetime',
            'archived_at' => 'datetime',
            'anonymized_at' => 'datetime',
            'legal_hold' => 'boolean',
            'signature_consented_at' => 'datetime',
            'gemini_api_key' => 'encrypted',
            'gemini_configured_at' => 'datetime',
        ];
    }

    /**
     * Relationship: A user can have many leads assigned to them.
     */
    public function leads()
    {
        return $this->hasMany(Lead::class, 'assigned_to');
    }

    /**
     * Relationship: A user can have many attendance logs.
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Relationship: A user can have many tasks.
     */
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function dailyReports()
    {
        return $this->hasMany(DailyReport::class);
    }

    public function leaveQuotas()
    {
        return $this->hasMany(LeaveQuota::class);
    }

    public function payslips()
    {
        return $this->hasMany(Payslip::class);
    }

    public function payrolls()
    {
        return $this->hasMany(Payroll::class);
    }

    public function separation()
    {
        return $this->hasOne(EmployeeSeparation::class);
    }

    public function alumniProfile()
    {
        return $this->hasOne(AlumniProfile::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function roleModel()
    {
        return $this->belongsTo(Role::class, 'role', 'key')->where('company_id', $this->company_id);
    }

    public function latestRoleAssignment()
    {
        return $this->hasOne(AuditLog::class, 'target_user_id')
            ->where('action', 'role_assigned')
            ->latest('created_at');
    }

    public function roleDetails()
    {
        return $this->belongsTo(Role::class, 'role', 'key')->where('company_id', $this->company_id);
    }

    public function companyMemberships()
    {
        return $this->hasMany(CompanyMembership::class);
    }

    public function reportsTo()
    {
        return $this->belongsTo(User::class, 'reports_to_id');
    }

    public function subordinates()
    {
        return $this->hasMany(User::class, 'reports_to_id');
    }

    public function defaultShift()
    {
        return $this->belongsTo(Shift::class, 'default_shift_id');
    }

    public function isAlumni(): bool
    {
        return $this->role === 'alumni' || $this->account_status === 'alumni';
    }

    public function isCEO()
    {
        return $this->role === 'ceo' || $this->isPlatformAdmin();
    }

    public function isPlatformAdmin(): bool
    {
        return in_array($this->role, ['platform_admin', 'super_admin', 'superadmin'], true);
    }

    public function isSuperAdmin(): bool
    {
        return in_array($this->role, ['super_admin', 'superadmin'], true);
    }

    public function belongsToCompany(Company $company): bool
    {
        if ($this->isPlatformAdmin()) {
            return true;
        }

        if ((int) $this->company_id === (int) $company->id) {
            return true;
        }

        return $this->companyMemberships()
            ->where('company_id', $company->id)
            ->where('is_active', true)
            ->exists();
    }

    public function isManager(): bool
    {
        if ($this->isAlumni()) {
            return false;
        }

        return str_starts_with((string) $this->role, 'mgr_');
    }

    public function isStaff(): bool
    {
        if ($this->isAlumni()) {
            return false;
        }

        return str_starts_with((string) $this->role, 'staff_');
    }

    public function isHRD(): bool
    {
        if ($this->isAlumni()) {
            return false;
        }

        return in_array($this->role, ['mgr_hrd', 'staff_hrd', 'hrd', 'hrd_manager', 'hr'], true);
    }

    public function isManagerOf(User $staff): bool
    {
        return $this->username === $staff->parent;
    }

    public function manager(): ?self
    {
        if (! $this->parent) {
            return null;
        }

        return self::query()
            ->where('username', $this->parent)
            ->where('is_active', true)
            ->first();
    }

    public function divisionKey(): ?string
    {
        return match (true) {
            str_contains((string) $this->role, 'marketing') => 'marketing',
            str_contains((string) $this->role, 'ops') => 'operasional',
            str_contains((string) $this->role, 'finance') => 'finance',
            str_contains((string) $this->role, 'hrd'),
            str_contains((string) $this->role, 'hr') => 'hrd',
            default => null,
        };
    }

    public function divisionLabel(): string
    {
        return match ($this->divisionKey()) {
            'marketing' => 'Marketing',
            'operasional' => 'Operasional',
            'finance' => 'Finance',
            'hrd' => 'HRD',
            default => 'Perusahaan',
        };
    }

    /**
     * Return the reporting chain above this user, nearest supervisor first.
     */
    public function managementChain(): Collection
    {
        $chain = collect();
        $seen = [];
        $cursor = $this;

        while ($cursor->parent && ! in_array($cursor->parent, $seen, true)) {
            $seen[] = $cursor->parent;
            $cursor = self::query()->where('username', $cursor->parent)->first();

            if (! $cursor) {
                break;
            }

            $chain->push($cursor);
        }

        return $chain;
    }

    public function getInitials(): string
    {
        $words = explode(' ', $this->name);
        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1).substr($words[1], 0, 1));
        }

        return strtoupper(substr($this->name, 0, 2));
    }

    public function hrProfile()
    {
        return $this->hasOne(HrEmployeeProfile::class, 'user_id');
    }

    public function hrContracts()
    {
        return $this->hasMany(HrEmployeeContract::class, 'user_id');
    }

    public function hrFamilies()
    {
        return $this->hasMany(HrEmployeeFamily::class, 'user_id');
    }

    public function hrEducations()
    {
        return $this->hasMany(HrEmployeeEducation::class, 'user_id');
    }

    public function hrCertifications()
    {
        return $this->hasMany(HrEmployeeCertification::class, 'user_id');
    }

    public function hrSkills()
    {
        return $this->hasMany(HrEmployeeSkill::class, 'user_id');
    }

    public function hrEmergencyContacts()
    {
        return $this->hasMany(HrEmployeeEmergencyContact::class, 'user_id');
    }

    public function hrDocuments()
    {
        return $this->hasMany(HrEmployeeDocument::class, 'user_id');
    }

    public function hrRosters()
    {
        return $this->hasMany(HrRoster::class, 'user_id');
    }

    public function hrAttendanceCorrections()
    {
        return $this->hasMany(HrAttendanceCorrection::class, 'user_id');
    }

    public function hrSalaryComponents()
    {
        return $this->hasMany(HrEmployeeSalaryComponent::class, 'user_id');
    }

    public function hrOkrs()
    {
        return $this->hasMany(HrOkr::class, 'user_id');
    }

    public function hrPerformanceReviews()
    {
        return $this->hasMany(HrPerformanceReview::class, 'user_id');
    }

    public function hrExitClearances()
    {
        return $this->hasMany(HrExitClearance::class, 'user_id');
    }
}
