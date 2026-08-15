<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrganizationChartController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var User $viewer */
        $viewer = $request->user();

        $people = User::query()
            ->where('is_active', true)
            ->where('account_status', 'active')
            ->whereNull('archived_at')
            ->orderByRaw("CASE WHEN role = 'ceo' THEN 0 WHEN role LIKE 'mgr_%' THEN 1 ELSE 2 END")
            ->orderBy('name')
            ->get()
            ->mapWithKeys(function (User $person) use ($viewer): array {
                $isSelf = $person->is($viewer);
                $isDirectReport = $viewer->isManagerOf($person);
                $canViewPerformance = $viewer->isCEO()
                    || $viewer->isHRD()
                    || $isSelf
                    || $isDirectReport;

                $data = [
                    'id' => $person->id,
                    'name' => $person->name,
                    'username' => $person->username,
                    'role' => $person->role,
                    'job_title' => $person->job_title ?: $this->defaultTitle($person),
                    'division' => $person->divisionKey() ?? 'company',
                    'division_label' => $person->divisionLabel(),
                    'level' => $this->level($person),
                    'parent' => $person->parent,
                    'employment_type' => $person->employment_type ?? 'Full-Time',
                    'avatar' => $this->initials($person->name),
                    'is_self' => $isSelf,
                    'is_direct_report' => $isDirectReport,
                    'can_view_performance' => $canViewPerformance,
                    'can_manage' => $viewer->isCEO()
                        || $viewer->isHRD()
                        || $isDirectReport,
                ];
                
                if ($canViewPerformance) {
                    $data['employee_code'] = $person->employee_code;
                }

                return [ $person->username => $data ];
            });

        return response()->json([
            'scope' => 'company_read_only',
            'privacy_notice' => 'Direktori ini hanya menampilkan informasi organisasi. Email, attendance, KPI, gaji, dan data pribadi tidak dibagikan.',
            'viewer' => [
                'can_manage_company' => $viewer->isCEO(),
                'can_manage_hr' => $viewer->isCEO() || $viewer->isHRD(),
                'can_manage_division' => $viewer->isManager(),
                'division' => $viewer->divisionKey(),
            ],
            'summary' => [
                'active_people' => $people->count(),
                'divisions' => $people->pluck('division')
                    ->reject(fn (string $division): bool => $division === 'company')
                    ->unique()
                    ->count(),
            ],
            'people' => $people,
        ]);
    }

    private function level(User $user): string
    {
        return match (true) {
            $user->isCEO() => 'CEO',
            $user->isManager() => 'Manager',
            default => $user->employment_type === 'Intern' ? 'Magang' : 'Staff',
        };
    }

    private function defaultTitle(User $user): string
    {
        return match ($user->role) {
            'ceo' => 'CEO & Founder',
            'mgr_marketing' => 'Marketing Manager',
            'staff_marketing' => 'Marketing Staff',
            'mgr_ops' => 'Operations Manager',
            'staff_ops' => 'Operations Staff',
            'mgr_finance' => 'Finance Manager',
            'staff_finance' => 'Finance Staff',
            'mgr_hrd' => 'HRD Manager',
            'staff_hrd' => 'HRD Staff',
            default => 'Anggota Tim',
        };
    }

    private function initials(string $name): string
    {
        return collect(preg_split('/\s+/', trim($name)) ?: [])
            ->filter()
            ->take(2)
            ->map(fn (string $part): string => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('') ?: 'SA';
    }
}
