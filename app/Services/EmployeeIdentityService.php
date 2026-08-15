<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EmployeeIdentityService
{
    public function createUser(array $attributes): User
    {
        return DB::transaction(function () use ($attributes): User {
            $user = User::query()->create([
                'name' => $attributes['name'],
                'username' => 'pending-'.Str::uuid(),
                'email' => $attributes['email'],
                'password' => Hash::make(Str::random(64)),
                'role' => $attributes['role'],
                'job_title' => $attributes['job_title'] ?? null,
                'is_active' => true,
                'parent' => $attributes['parent'] ?? null,
                'employment_type' => $attributes['employment_type'] ?? 'Full-Time',
            ]);

            $user->forceFill([
                'username' => $this->loginUsername($user),
                'employee_code' => $this->employeeCode($user),
            ])->save();

            return $user->fresh();
        });
    }

    public function preview(
        string $role,
        string $employmentType = 'Full-Time',
        string $name = 'pegawai',
    ): array {
        $sequence = ((int) User::query()->max('id')) + 1;

        return [
            'username' => $this->loginUsernameFrom(
                $role,
                $employmentType,
                $name,
                $sequence,
            ),
            'employee_code' => $this->employeeCodeFrom(
                $role,
                $employmentType,
                $sequence,
            ),
        ];
    }

    public function refreshEmployeeCode(User $user): User
    {
        $user->forceFill([
            'employee_code' => $this->employeeCode($user),
        ])->save();

        return $user->fresh();
    }

    private function loginUsername(User $user): string
    {
        return $this->loginUsernameFrom(
            (string) $user->role,
            (string) $user->employment_type,
            (string) $user->name,
            (int) $user->id,
        );
    }

    private function loginUsernameFrom(
        string $role,
        string $employmentType,
        string $name,
        int $sequence,
    ): string {
        $segments = $this->organizationSegments($role, $employmentType);
        $nameSlug = Str::slug($name);
        $nameSlug = $nameSlug !== '' ? Str::limit($nameSlug, 28, '') : 'pegawai';

        return sprintf(
            'sa.%s.%s.%s.%04d',
            strtolower($segments['division']),
            strtolower($segments['level']),
            $nameSlug,
            $sequence,
        );
    }

    private function employeeCode(User $user): string
    {
        return $this->employeeCodeFrom(
            (string) $user->role,
            (string) $user->employment_type,
            (int) $user->id,
        );
    }

    private function employeeCodeFrom(string $role, string $employmentType, int $sequence): string
    {
        $segments = $this->organizationSegments($role, $employmentType);

        return sprintf(
            'SA-%s-%s-%04d',
            $segments['division'],
            $segments['level'],
            $sequence,
        );
    }

    private function organizationSegments(string $role, string $employmentType): array
    {
        $division = match (true) {
            str_contains($role, 'marketing') => 'MKT',
            str_contains($role, 'ops') => 'OPS',
            str_contains($role, 'finance') => 'FIN',
            str_contains($role, 'hrd') => 'HRD',
            $role === 'ceo' => 'EXE',
            default => 'GEN',
        };
        $level = match (true) {
            str_contains(strtolower($employmentType), 'intern') => 'INT',
            $role === 'ceo' => 'CEO',
            str_starts_with($role, 'mgr_') => 'MGR',
            default => 'STF',
        };

        return compact('division', 'level');
    }
}
