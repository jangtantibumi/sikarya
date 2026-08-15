<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmployeeSeparation;
use App\Models\User;
use App\Services\EmployeeIdentityService;
use App\Services\EmployeeSeparationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function __construct(
        private readonly EmployeeIdentityService $identity,
        private readonly EmployeeSeparationService $separations,
    ) {}

    public function index(Request $request)
    {
        $viewer = $request->user();
        $users = User::query()
            ->where('is_active', true)
            ->where('account_status', 'active')
            ->when(
                ! $viewer->isCEO() && ! $viewer->isHRD(),
                function ($query) use ($viewer): void {
                    $visibleUsernames = collect([$viewer->username])
                        ->merge($viewer->managementChain()->pluck('username'));

                    $query->where(function ($visible) use ($viewer, $visibleUsernames): void {
                        $visible->whereIn('username', $visibleUsernames);

                        if ($viewer->isManager()) {
                            $visible->orWhere('parent', $viewer->username);
                        }
                    });
                },
            )
            ->orderBy('id')
            ->get();

        return response()->json(
            $users->mapWithKeys(fn (User $user): array => [
                $user->username => $this->formatUser($user),
            ])
        );
    }

    public function store(Request $request)
    {
        abort_unless($request->user()?->isCEO() || $request->user()?->isHRD(), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'string', 'in:ceo,mgr_marketing,staff_marketing,mgr_ops,staff_ops,mgr_finance,staff_finance,mgr_hrd,staff_hrd'],
            'job_title' => ['required', 'string', 'max:120'],
            'parent' => ['nullable', 'string', 'exists:users,username'],
            'employment_type' => ['nullable', 'string', 'max:100'],
        ]);

        $user = $this->identity->createUser($validated);

        return response()->json([
            'success' => true,
            'message' => 'Pengguna berhasil ditambahkan.',
            'user' => $this->formatUser($user),
        ], 201);
    }

    public function previewIdentity(Request $request)
    {
        abort_unless(
            $request->user()?->isCEO()
                || $request->user()?->isHRD()
                || $request->user()?->isManager(),
            403,
        );
        $validated = $request->validate([
            'role' => ['required', 'string', 'in:ceo,mgr_marketing,staff_marketing,mgr_ops,staff_ops,mgr_finance,staff_finance,mgr_hrd,staff_hrd'],
            'employment_type' => ['nullable', 'string', 'max:100'],
            'name' => ['nullable', 'string', 'max:255'],
        ]);

        return response()->json([
            'identity' => $this->identity->preview(
                $validated['role'],
                $validated['employment_type'] ?? 'Full-Time',
                $validated['name'] ?? 'pegawai',
            ),
        ]);
    }

    public function update(Request $request, string $username)
    {
        $user = User::query()->where('username', $username)->firstOrFail();
        $actor = $request->user();
        $canManage = $actor->isCEO()
            || $actor->isHRD()
            || ($actor->isManager() && $actor->isManagerOf($user));
        $isSelf = $actor->id === $user->id;

        abort_unless($canManage || $isSelf, 403);

        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'employment_type' => ['nullable', 'string', 'max:100'],
            'job_title' => ['nullable', 'string', 'max:120'],
        ]);

        if (! $canManage) {
            unset($validated['job_title'], $validated['employment_type']);
        }

        $user->fill(array_filter(
            $validated,
            fn ($value): bool => $value !== null && $value !== ''
        ))->save();

        return response()->json([
            'success' => true,
            'message' => "Profil @{$user->username} berhasil diperbarui.",
            'user' => $this->formatUser($user->fresh()),
        ]);
    }

    public function destroy(Request $request, string $username)
    {
        $actor = $request->user();
        abort_unless($actor?->isCEO(), 403);

        $validated = $request->validate([
            'completion_status' => ['required', 'string', 'in:completed,incomplete'],
            'convert_to_alumni' => ['sometimes', 'boolean'],
            'separation_reason' => ['required', 'string', 'in:completed,terminated,resigned,other'],
            'separation_notes' => ['nullable', 'string', 'max:2000', 'required_if:separation_reason,other'],
            'effective_date' => ['required', 'date', 'before_or_equal:today'],
        ]);

        $user = User::query()->where('username', $username)->firstOrFail();
        abort_if($user->isCEO(), 422, 'Akun CEO tidak dapat dinonaktifkan.');

        $separation = $this->separations->separate(
            $user,
            $actor,
            $actor,
            $validated,
            request: $request,
        );

        return response()->json([
            'success' => true,
            'message' => 'Pengguna dinonaktifkan dan langsung dihapus dari hirarki, sidebar, serta live attendance.',
            'separation' => [
                'id' => $separation->id,
                'completion_status' => $separation->completion_status,
                'converted_to_alumni' => $separation->converted_to_alumni,
                'separation_reason' => $separation->separation_reason,
                'effective_date' => $separation->effective_date?->format('Y-m-d'),
                'backup_download_url' => route('employee-separations.backup', $separation),
            ],
        ]);
    }

    public function downloadSeparationBackup(Request $request, EmployeeSeparation $employeeSeparation)
    {
        $actor = $request->user();
        abort_unless($actor->isCEO() || $actor->isHRD() || $employeeSeparation->initiated_by_id === $actor->id, 403);
        abort_unless($employeeSeparation->backup_path && Storage::disk('local')->exists($employeeSeparation->backup_path), 404);

        return Storage::disk('local')->download(
            $employeeSeparation->backup_path,
            'arsip-karyawan-'.$employeeSeparation->user_id.'.json',
            ['Content-Type' => 'application/json; charset=UTF-8'],
        );
    }

    private function formatUser(User $user): array
    {
        $roleTitles = [
            'mgr_marketing' => 'Marketing Manager',
            'staff_marketing' => 'Marketing Staff',
            'mgr_ops' => 'Operations Manager',
            'staff_ops' => 'Operations Staff',
            'mgr_finance' => 'Finance Manager',
            'staff_finance' => 'Finance Staff',
            'mgr_hrd' => 'HRD Manager',
            'staff_hrd' => 'HRD Staff',
            'ceo' => 'CEO & Founder',
        ];

        return [
            'name' => $user->name,
            'username' => $user->username,
            'employee_code' => $user->employee_code,
            'email' => $user->email,
            'role' => $user->role,
            'level' => $user->isCEO()
                ? 'Level 1 - CEO'
                : ($user->isManager() ? 'Level 2 - Manager' : 'Level 3 - Staff'),
            'parent' => $user->parent ?? 'ceo',
            'avatar' => strtoupper(substr($user->username, 0, 2)),
            'title' => $user->job_title ?: ($roleTitles[$user->role] ?? 'Staff Member'),
            'job_title' => $user->job_title,
            'employment_type' => $user->employment_type ?? 'Full-Time',
            'archived_at' => $user->archived_at?->toIso8601String(),
            'account_status' => $user->account_status,
        ];
    }
}
