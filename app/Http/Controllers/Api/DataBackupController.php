<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApprovalRequest;
use App\Models\AlumniProfile;
use App\Models\Attendance;
use App\Models\ChatMessage;
use App\Models\ClientInflow;
use App\Models\Goal;
use App\Models\KpiPlan;
use App\Models\JournalEntry;
use App\Models\Lead;
use App\Models\LeaveRequest;
use App\Models\ErpDocument;
use App\Models\EmployeeSeparation;
use App\Models\Project;
use App\Models\ResignationRequest;
use App\Models\Task;
use App\Models\TalentReview;
use App\Models\TeamRequest;
use App\Models\User;
use App\Services\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class DataBackupController extends Controller
{
    public function __construct(private readonly TenantContext $tenant)
    {
    }
    public function show(Request $request)
    {
        $viewer = $request->user();
        $visibleUserIds = $this->visibleUsers($viewer)->pluck('id');
        $visibleUsernames = User::query()->whereIn('id', $visibleUserIds)->pluck('username');
        $division = $viewer->divisionKey();

        $approvalQuery = ApprovalRequest::query()->with(['requester:id,name,username,role', 'steps.approver:id,name,username']);
        if (!$viewer->isCEO() && !$viewer->isHRD()) {
            $approvalQuery->whereIn('requester_id', $visibleUserIds);
        } elseif ($viewer->isHRD() && !$viewer->isCEO()) {
            $approvalQuery->whereIn('request_type', ['leave', 'resignation', 'team_request']);
        }

        $payload = [
            'backup_info' => [
                'generated_at' => now()->toIso8601String(),
                'generated_by' => $viewer->only(['name', 'username', 'role', 'job_title']),
                'scope' => $viewer->isCEO() ? 'company' : ($viewer->isHRD() ? 'human_resources' : ($viewer->isManager() ? 'division_team' : 'personal')),
            ],
            'profile_and_visible_team' => $this->visibleUsers($viewer)->map(fn (User $user) => [
                'name' => $user->name,
                'username' => $user->username,
                'role' => $user->role,
                'job_title' => $user->job_title,
                'division' => $user->divisionLabel(),
                'parent' => $user->parent,
                'employment_type' => $user->employment_type,
                'is_active' => $user->is_active,
            ]),
            'tasks' => Task::query()->with(['user:id,name,username', 'kpi:id,title'])->whereIn('user_id', $visibleUserIds)->get(),
            'attendance' => Attendance::query()->with('user:id,name,username')->whereIn('user_id', $visibleUserIds)->latest('clock_in')->get(),
            'leave_requests' => LeaveRequest::query()->with('user:id,name,username')->whereIn('user_id', $visibleUserIds)->latest('id')->get(),
            'resignation_requests' => ResignationRequest::query()->with('user:id,name,username')->whereIn('user_id', $visibleUserIds)->latest('id')->get(),
            'approvals' => $approvalQuery->latest('id')->get(),
            'goals' => Goal::query()->when(!$viewer->isCEO(), fn (Builder $query) => $query->where('division', $division))->get(),
            'kpi_plans' => KpiPlan::query()
                ->with(['goal:id,title,division', 'manager:id,name,username', 'kpis'])
                ->when(!$viewer->isCEO(), fn (Builder $query) => $query->whereHas('goal', fn (Builder $goal) => $goal->where('division', $division)))
                ->get(),
            'team_requests' => TeamRequest::query()
                ->when(!$viewer->isCEO() && !$viewer->isHRD(), fn (Builder $query) => $query->whereIn('requester_username', $visibleUsernames))
                ->get()
                ->map(fn (TeamRequest $item) => array_merge($item->toArray(), [
                    'details' => collect($item->details ?? [])->except(['password'])->all(),
                ])),
            'chat_messages' => ChatMessage::query()
                ->with('sender:id,name,username')
                ->whereIn('channel', $this->chatChannels($viewer))
                ->latest('id')
                ->limit(1000)
                ->get()
                ->makeHidden(['attachment_path']),
            'talent_reviews' => TalentReview::query()
                ->with(['user:id,name,username', 'reviewer:id,name,username'])
                ->whereIn('user_id', $visibleUserIds)
                ->when($viewer->isStaff(), fn (Builder $query) => $query->where('status', 'published'))
                ->get(),
            'documents' => ErpDocument::query()
                ->with(['owner:id,name,username', 'creator:id,name,username', 'signatures.signer:id,name,username'])
                ->when(
                    !$viewer->isCEO() && !$viewer->isHRD(),
                    fn (Builder $query) => $query->whereIn('owner_user_id', $visibleUserIds),
                )
                ->get()
                ->makeHidden(['verification_token']),
        ];

        if ($viewer->isAlumni()) {
            $payload['alumni_profile'] = AlumniProfile::query()
                ->where('user_id', $viewer->id)
                ->first();
        } elseif ($viewer->isCEO() || $viewer->isHRD()) {
            $payload['alumni_directory'] = AlumniProfile::query()
                ->with('user:id,name,username,email,employee_code,alumni_since')
                ->latest('id')
                ->get();
        }

        if ($viewer->isCEO() || $division === 'marketing') {
            $payload['leads'] = Lead::query()
                ->with('assignee:id,name,username')
                ->when(!$viewer->isCEO(), fn (Builder $query) => $query->whereIn('assigned_to', $visibleUserIds))
                ->get();
        }

        if ($viewer->isCEO() || $division === 'finance') {
            $payload['client_inflows'] = ClientInflow::query()
                ->when(!$viewer->isCEO(), fn (Builder $query) => $query->whereIn('created_by', $visibleUsernames))
                ->get();
            $payload['journal_entries'] = JournalEntry::query()
                ->with([
                    'lines.account:id,code,name,type',
                    'lines.project:id,code,name',
                    'attachments:id,attachable_type,attachable_id,original_name,mime_type,size_bytes,created_at',
                ])
                ->latest('entry_date')
                ->get();
        }

        if ($viewer->isCEO() || in_array($division, ['operasional', 'finance'], true)) {
            $payload['projects'] = Project::query()
                ->with([
                    'manager:id,name,username',
                    'costs.creator:id,name,username',
                    'costs.attachments:id,attachable_type,attachable_id,original_name,mime_type,size_bytes,created_at',
                ])
                ->get();
        }

        if ($viewer->isCEO() || $viewer->isHRD()) {
            $payload['employee_separations'] = EmployeeSeparation::query()
                ->with([
                    'user:id,name,username,role,job_title',
                    'initiator:id,name,username',
                    'approver:id,name,username',
                ])
                ->latest('effective_date')
                ->get();
        }

        $filename = 'suba-arch-backup-' . $viewer->username . '-' . now()->format('Y-m-d-His') . '.json';
        $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($request->boolean('download')) {
            return response($json, 200, [
                'Content-Type' => 'application/json; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ]);
        }

        return response()->json([
            'filename' => $filename,
            'data' => $payload,
            'server_time' => now()->toIso8601String(),
        ]);
    }

    private function visibleUsers(User $viewer)
    {
        if ($viewer->isAlumni()) {
            return User::query()->whereKey($viewer->id)->get();
        }

        $query = User::query()
            ->where('is_active', true)
            ->where('account_status', 'active');

        if ($this->tenant->id()) {
            $query->where('company_id', $this->tenant->id());
        }

        if ($viewer->isCEO() || $viewer->isHRD()) {
            return $query->orderBy('id')->get();
        }

        if ($viewer->isManager()) {
            return $query
                ->where(fn (Builder $users) => $users->whereKey($viewer->id)->orWhere('parent', $viewer->username))
                ->orderBy('id')
                ->get();
        }

        return $query->whereKey($viewer->id)->get();
    }

    private function chatChannels(User $viewer): array
    {
        if ($viewer->isAlumni()) {
            return [];
        }

        $channels = ['general'];
        if ($viewer->isCEO()) {
            return ['general', 'marketing-team', 'operations-team', 'finance-team', 'hr-team', 'management'];
        }
        if (in_array($viewer->role, ['mgr_marketing', 'staff_marketing'], true)) $channels[] = 'marketing-team';
        if (in_array($viewer->role, ['mgr_ops', 'staff_ops'], true)) $channels[] = 'operations-team';
        if (in_array($viewer->role, ['mgr_finance', 'staff_finance'], true)) $channels[] = 'finance-team';
        if ($viewer->isHRD()) $channels[] = 'hr-team';
        if ($viewer->isManager()) $channels[] = 'management';

        return array_values(array_unique($channels));
    }
}
