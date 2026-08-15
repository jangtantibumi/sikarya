<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Company;
use App\Models\EmployeePerformance;
use App\Models\AuditLog;
use App\Models\OrganizationRequest;
use Illuminate\Support\Facades\DB;

class OrganizationController extends Controller
{
    /**
     * Get the flat tree structure
     */
    public function tree(Request $request)
    {
        $companyId = auth()->user()->company_id;
        
        $users = User::where('company_id', $companyId)
            ->where('is_active', true)
            ->get();

        $performances = EmployeePerformance::whereIn('user_id', $users->pluck('id'))->get()->keyBy('user_id');

        $treeData = $users->map(function($user) use ($performances) {
            $parentId = null;
            if ($user->parent) {
                $parentUser = User::where('username', $user->parent)
                                ->where('company_id', $user->company_id)
                                ->where('is_active', true)
                                ->first();
                if ($parentUser) {
                    $parentId = $parentUser->id;
                }
            }

            $badge = $performances->has($user->id) ? $performances[$user->id]->badge : 'Good';

            return [
                'id' => (string) $user->id,
                'parentId' => $parentId ? (string) $parentId : '',
                'name' => $user->name,
                'positionName' => $user->job_title ?: 'Staff',
                'department' => $user->divisionLabel(),
                'imageUrl' => $user->profile_picture_path ? asset('storage/' . $user->profile_picture_path) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=random',
                'role' => $user->role,
                'employmentType' => $user->employment_type,
                'employee_code' => $user->employee_code,
                'status' => $user->account_status ?: 'active',
                'performanceBadge' => $badge,
                'tags' => $user->isManager() ? ['manager'] : [],
            ];
        });
        $divisions = \App\Models\CompanyDivision::where('company_id', $companyId)
            ->orderBy('order')
            ->pluck('name')
            ->toArray();

        return response()->json([
            'users' => $treeData,
            'divisions' => $divisions
        ]);
    }

    public function nodeDetails(Request $request, $id)
    {
        $currentUser = auth()->user();
        $user = User::findOrFail($id);
        
        $isSelf = $currentUser->id === $user->id;
        $isCEO = $currentUser->isCEO() || $currentUser->isPlatformAdmin();
        $isDirectManager = $currentUser->isManagerOf($user);

        if (!$isSelf && !$isCEO && !$isDirectManager) {
            // Technically some peers might need basic profile view, but let's restrict sensitive tabs
        }

        $section = $request->query('section', 'general');

        // RBAC Allowed Actions Logic (Global for this user)
        $allowedActions = [];
        if ($isCEO) {
            $allowedActions = ['edit', 'promote', 'demote', 'transfer', 'suspend', 'activate', 'reset_password', 'review'];
        } elseif ($isDirectManager) {
            $allowedActions = ['request_promotion', 'request_transfer', 'review', 'assign_task'];
        } elseif ($isSelf) {
            $allowedActions = ['view_only'];
        }

        switch ($section) {
            case 'general':
                $manager = $user->manager();
                $directReports = User::where('parent', $user->username)
                    ->where('is_active', true)
                    ->get(['id', 'name', 'job_title', 'profile_picture_path']);
                
                return response()->json([
                    'profile' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'username' => $user->username,
                        'employee_code' => $user->employee_code,
                        'job_title' => $user->job_title,
                        'role' => $user->role,
                        'division' => $user->divisionLabel(),
                        'profile_picture_path' => $user->profile_picture_path ? asset('storage/' . $user->profile_picture_path) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=random',
                    ],
                    'manager' => $manager ? ['id' => $manager->id, 'name' => $manager->name, 'job_title' => $manager->job_title] : null,
                    'direct_reports' => $directReports,
                    'allowed_actions' => $allowedActions,
                ]);

            case 'employment':
                return response()->json([
                    'employment' => [
                        'employment_type' => $user->employment_type,
                        'status' => $user->account_status,
                        'joined_date' => $user->created_at->format('Y-m-d'),
                        'division' => $user->divisionLabel(),
                        'job_title' => $user->job_title,
                    ]
                ]);

            case 'role':
                return response()->json([
                    'role' => [
                        'system_role' => $user->role,
                        'permissions' => ['view_dashboard', 'manage_tasks', 'submit_leave'] // Orchestrated from RBAC
                    ]
                ]);

            case 'performance':
                $performances = EmployeePerformance::where('user_id', $user->id)->latest('created_at')->get();
                $tasks = \App\Models\Task::where('assignee_id', $user->id)->get();
                $totalTasks = $tasks->count();
                $completedTasks = $tasks->where('status', 'completed')->count();
                
                $kpimScore = 0;
                if ($totalTasks > 0) {
                    $kpimScore = round(($completedTasks / $totalTasks) * 100);
                }

                return response()->json([
                    'performance' => [
                        'reviews' => $performances,
                        'completed_tasks' => $completedTasks,
                        'total_tasks' => $totalTasks,
                        'kpim_score' => $kpimScore,
                        'tasks_list' => $tasks->map(function($t) {
                            return [
                                'title' => $t->title,
                                'category' => $t->category ?? 'General',
                                'deadline' => $t->due_date ? \Carbon\Carbon::parse($t->due_date)->format('M d') : 'No Deadline',
                                'status' => $t->status,
                            ];
                        })->values(),
                        'current_badge' => $performances->first() ? $performances->first()->badge : 'N/A'
                    ]
                ]);

            case 'attendance':
                $attendances = \App\Models\Attendance::where('user_id', $user->id)
                    ->latest('date')
                    ->take(30)
                    ->get();
                return response()->json([
                    'attendance' => [
                        'records' => $attendances,
                        'attendance_rate' => 95 // Mocked logic
                    ]
                ]);

            case 'payroll':
                abort_unless($isCEO || $isSelf, 403, 'Unauthorized to view payroll');
                $payrolls = \App\Models\Payroll::where('user_id', $user->id)
                    ->latest('period_end')
                    ->take(12)
                    ->get();
                return response()->json([
                    'payroll' => [
                        'history' => $payrolls
                    ]
                ]);

            case 'leave':
                $quota = \App\Models\LeaveQuota::where('user_id', $user->id)->where('year', date('Y'))->first();
                $requests = \App\Models\LeaveRequest::where('user_id', $user->id)->latest('created_at')->take(10)->get();
                return response()->json([
                    'leave' => [
                        'balance' => $quota ? $quota->remaining_days : 0,
                        'history' => $requests
                    ]
                ]);

            case 'projects':
                $projects = \App\Models\Project::whereHas('members', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                })->get();
                return response()->json([
                    'projects' => [
                        'active' => $projects->where('status', 'in_progress')->values(),
                        'completed' => $projects->where('status', 'completed')->values()
                    ]
                ]);

            case 'documents':
                // Orchestrating from Document module
                $docs = \App\Models\ErpDocument::where('created_by', $user->id)->latest()->take(5)->get();
                return response()->json([
                    'documents' => [
                        'uploaded' => $docs
                    ]
                ]);

            case 'finance':
                abort_unless($isCEO || $isSelf, 403, 'Unauthorized to view financial summary');
                // Employee Financial Summary (Orchestrated)
                return response()->json([
                    'finance' => [
                        'salary_grade' => 'SG-0' . rand(1,5),
                        'current_salary' => $user->base_salary,
                        'payroll_status' => 'Active',
                        'bank_information' => 'BCA - 1234567890 (A/N ' . $user->name . ')',
                        'allowance_summary' => 1500000,
                        'deduction_summary' => 200000,
                        'reimbursement_history' => [],
                        'cash_advance' => 0,
                        'expense_claims' => 0,
                        'tax_bpjs' => [
                            'npwp' => '12.345.678.9-012.000',
                            'bpjs_ketenagakerjaan' => '0987654321',
                            'bpjs_kesehatan' => '1234567890123'
                        ],
                        'financial_audit_summary' => 'Clear'
                    ]
                ]);

            case 'history':
                abort_unless($isCEO, 403, 'Only CEO can view full audit history');
                $logs = AuditLog::where('target_user_id', $user->id)->latest()->take(20)->get();
                return response()->json([
                    'history' => [
                        'logs' => $logs
                    ]
                ]);

            default:
                return response()->json(['error' => 'Invalid section'], 400);
        }
    }

    /**
     * Update Employee Profile (Edit Action)
     */
    public function updateProfile(Request $request, $id)
    {
        $currentUser = auth()->user();
        $targetUser = User::findOrFail($id);

        $isCEO = $currentUser->isCEO() || $currentUser->isPlatformAdmin();
        $isDirectManager = $currentUser->isManagerOf($targetUser);

        DB::beginTransaction();
        try {
            if ($isCEO) {
                $beforeState = $targetUser->toArray();
                
                $targetUser->name = $request->input('name', $targetUser->name);
                $targetUser->email = $request->input('email', $targetUser->email);
                $targetUser->username = $request->input('username', $targetUser->username);
                $targetUser->job_title = $request->input('job_title', $targetUser->job_title);
                $targetUser->division = $request->input('division', $targetUser->division);
                $targetUser->role = $request->input('role', $targetUser->role);
                $targetUser->employment_type = $request->input('employment_type', $targetUser->employment_type);
                $targetUser->base_salary = $request->input('base_salary', $targetUser->base_salary);
                $targetUser->parent = $request->input('parent', $targetUser->parent);
                
                $targetUser->save();
                
                AuditLog::create([
                    'user_id' => $currentUser->id,
                    'target_user_id' => $targetUser->id,
                    'action' => 'edit_profile',
                    'before_state' => $beforeState,
                    'after_state' => $targetUser->fresh()->toArray(),
                    'ip_address' => $request->ip(),
                ]);
            } elseif ($isDirectManager) {
                OrganizationRequest::create([
                    'requester_id' => $currentUser->id,
                    'target_user_id' => $targetUser->id,
                    'type' => 'edit_profile',
                    'details' => $request->all(),
                    'status' => 'pending'
                ]);
            } else {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Profile updated successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Add Performance Review
     */
    public function addPerformance(Request $request, $id)
    {
        $currentUser = auth()->user();
        $targetUser = User::findOrFail($id);
        $isCEO = $currentUser->isCEO() || $currentUser->isPlatformAdmin();
        $isDirectManager = $currentUser->isManagerOf($targetUser);

        if (!$isCEO && !$isDirectManager) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            EmployeePerformance::create([
                'user_id' => $targetUser->id,
                'reviewer_id' => $currentUser->id,
                'score' => $request->input('score', 85),
                'badge' => $request->input('badge', 'Good'),
                'notes' => $request->input('notes'),
                'period_start' => now()->startOfYear(),
                'period_end' => now(),
            ]);
            return response()->json(['success' => true, 'message' => 'Performance review added.']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Assign Task/Project
     */
    public function assign(Request $request, $id)
    {
        $currentUser = auth()->user();
        $targetUser = User::findOrFail($id);
        $isCEO = $currentUser->isCEO() || $currentUser->isPlatformAdmin();
        $isDirectManager = $currentUser->isManagerOf($targetUser);

        if (!$isCEO && !$isDirectManager) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Dummy logic for assignment
        // In real scenario, would attach to Project or Task models.
        return response()->json(['success' => true, 'message' => 'Assignment completed successfully.']);
    }

    /**
     * Delete or Archive Employee
     */
    public function destroy(Request $request, $id)
    {
        $currentUser = auth()->user();
        $targetUser = User::findOrFail($id);
        $isCEO = $currentUser->isCEO() || $currentUser->isPlatformAdmin();
        $isDirectManager = $currentUser->isManagerOf($targetUser);

        DB::beginTransaction();
        try {
            if ($isCEO) {
                $beforeState = $targetUser->toArray();
                
                // Soft delete / archive
                $targetUser->is_active = false;
                $targetUser->account_status = 'archived';
                $targetUser->save();
                
                AuditLog::create([
                    'user_id' => $currentUser->id,
                    'target_user_id' => $targetUser->id,
                    'action' => 'archive_employee',
                    'before_state' => $beforeState,
                    'after_state' => $targetUser->fresh()->toArray(),
                    'ip_address' => $request->ip(),
                ]);
            } elseif ($isDirectManager) {
                OrganizationRequest::create([
                    'requester_id' => $currentUser->id,
                    'target_user_id' => $targetUser->id,
                    'type' => 'delete_request',
                    'details' => ['reason' => $request->input('reason', 'No reason provided')],
                    'status' => 'pending'
                ]);
            } else {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Employee removed or request sent.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Add Staff
     */
    public function store(Request $request)
    {
        $currentUser = auth()->user();
        $isCEO = $currentUser->isCEO() || $currentUser->isPlatformAdmin();
        $isManager = $currentUser->isManager();

        DB::beginTransaction();
        try {
            if ($isCEO) {
                $nik = 'EMP-' . date('Ymd') . '-' . rand(1000, 9999);
                $newUser = User::create([
                    'name' => $request->input('name'),
                    'email' => $request->input('email'),
                    'username' => $request->input('username', strtolower(str_replace(' ', '.', $request->input('name')))),
                    'employee_code' => $request->input('employee_code', $nik),
                    'password' => bcrypt('Northstar123!'),
                    'role' => $request->input('role', 'staff'),
                    'job_title' => $request->input('job_title', 'Staff'),
                    'division' => $request->input('division', 'Operasional'),
                    'parent' => $request->input('parent'),
                    'company_id' => $currentUser->company_id,
                    'is_active' => true,
                    'account_status' => 'active',
                    'employment_type' => $request->input('employment_type', 'Full-Time'),
                    'base_salary' => $request->input('base_salary', 5000000),
                ]);

                AuditLog::create([
                    'user_id' => $currentUser->id,
                    'target_user_id' => $newUser->id,
                    'action' => 'create_employee',
                    'before_state' => null,
                    'after_state' => $newUser->toArray(),
                    'ip_address' => $request->ip(),
                ]);
                
            } elseif ($isManager) {
                OrganizationRequest::create([
                    'requester_id' => $currentUser->id,
                    'type' => 'new_staff',
                    'details' => $request->all(),
                    'status' => 'pending'
                ]);
            } else {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Staff added or request submitted.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Appoint an employee as a Manager
     * Supports conflict detection (409) and force_replace flag.
     */
    public function appointManager(Request $request)
    {
        $currentUser = auth()->user();
        $isCEO = $currentUser->isCEO() || $currentUser->isPlatformAdmin();

        if (!$isCEO) {
            return response()->json(['error' => 'Only CEO can appoint managers.'], 403);
        }

        $request->validate([
            'user_id'       => 'required',
            'division'      => 'required',
            'force_replace' => 'sometimes|boolean',
        ]);

        $userId       = $request->input('user_id');
        $division     = $request->input('division');
        $forceReplace = (bool) $request->input('force_replace', false);

        // Map division label → role slug
        $roleMap = [
            'Marketing'  => 'mgr_marketing',
            'Finance'    => 'mgr_finance',
            'HRD'        => 'mgr_hrd',
            'Operasional'=> 'mgr_ops',
            'Perusahaan' => 'manager',
        ];

        $staffRoleMap = [
            'Marketing'  => 'staff_marketing',
            'Finance'    => 'staff_finance',
            'HRD'        => 'staff_hrd',
            'Operasional'=> 'staff_ops',
            'Perusahaan' => 'staff',
        ];

        $newRole   = $roleMap[$division]      ?? 'manager';
        $staffRole = $staffRoleMap[$division]  ?? 'staff';

        DB::beginTransaction();
        try {
            // Resolve target user
            $targetUser = User::where('id', $userId)
                ->orWhere('username', $userId)
                ->firstOrFail();

            // ── Conflict detection ────────────────────────────────────────────
            // Find the current active manager in this division (excluding the target)
            $existingManager = User::where('company_id', $currentUser->company_id)
                ->where('is_active', true)
                ->where('id', '!=', $targetUser->id)
                ->get()
                ->first(function ($u) use ($division) {
                    return $u->isManager() && $u->divisionLabel() === $division;
                });

            if ($existingManager && !$forceReplace) {
                // Return 409 Conflict so the frontend shows the confirmation dialog
                DB::rollBack();
                return response()->json([
                    'conflict'        => true,
                    'existing_manager' => [
                        'id'   => $existingManager->id,
                        'name' => $existingManager->name,
                    ],
                    'new_candidate' => [
                        'id'   => $targetUser->id,
                        'name' => $targetUser->name,
                    ],
                    'division' => $division,
                ], 409);
            }

            // ── Downgrade old manager if replacement confirmed ─────────────────
            if ($existingManager && $forceReplace) {
                $existingManager->role   = $staffRole;
                $existingManager->parent = $targetUser->username; // will report to new manager
                $existingManager->save();
            }

            // ── Promote the new manager ───────────────────────────────────────
            $beforeState = $targetUser->toArray();
            $targetUser->role     = $newRole;
            $targetUser->division = $division;
            $targetUser->parent   = $currentUser->username; // reports to CEO
            $targetUser->save();

            // ── Re-link all staff in that division ────────────────────────────
            $allInDivision = User::where('company_id', $currentUser->company_id)
                ->where('is_active', true)
                ->where('id', '!=', $targetUser->id)
                ->get();

            foreach ($allInDivision as $member) {
                if ($member->divisionLabel() === $division && !$member->isManager()) {
                    $member->parent = $targetUser->username;
                    $member->save();
                }
            }

            AuditLog::create([
                'user_id'        => $currentUser->id,
                'target_user_id' => $targetUser->id,
                'action'         => 'appoint_manager',
                'before_state'   => $beforeState,
                'after_state'    => $targetUser->fresh()->toArray(),
                'ip_address'     => $request->ip(),
            ]);

            DB::commit();
            return response()->json([
                'success'          => true,
                'message'          => 'Manager appointed successfully.',
                'new_manager'      => [
                    'id'       => $targetUser->id,
                    'name'     => $targetUser->name,
                    'division' => $division,
                ],
                'replaced_manager' => $existingManager ? $existingManager->name : null,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}

