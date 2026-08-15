<?php

namespace App\Services;

use App\Models\ApprovalRequest;
use App\Models\Attendance;
use App\Models\ChatMessage;
use App\Models\Goal;
use App\Models\KpiPlan;
use App\Models\Lead;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class GeminiContextService
{
    public function __construct(
        private readonly ChatChannelService $channels,
    ) {
    }

    public function for(User $viewer, ?string $channel = null): array
    {
        if ($channel && !$this->channels->canAccess($viewer, $channel)) {
            abort(403, 'Anda tidak memiliki akses ke konteks kanal tersebut.');
        }

        $visibleUsers = $this->visibleUsers($viewer);
        $visibleIds = $visibleUsers->pluck('id');
        $division = $viewer->divisionKey();

        $approvalQuery = ApprovalRequest::query()
            ->with('requester:id,name,username')
            ->whereIn('status', ['pending_manager', 'pending_ceo']);

        if ($viewer->isCEO()) {
            // CEO may see the current company-wide approval queue.
        } elseif ($viewer->isManager()) {
            $approvalQuery->where(function (Builder $query) use ($viewer, $visibleIds): void {
                $query->whereIn('requester_id', $visibleIds)
                    ->orWhere('current_approver_id', $viewer->id);
            });
        } elseif ($viewer->isHRD()) {
            $approvalQuery->whereIn('request_type', ['leave', 'resignation', 'team_request']);
        } else {
            $approvalQuery->where('requester_id', $viewer->id);
        }

        $context = [
            'generated_at' => now()->toIso8601String(),
            'viewer' => [
                'name' => $viewer->name,
                'username' => $viewer->username,
                'role' => $viewer->role,
                'job_title' => $viewer->job_title,
                'division' => $viewer->divisionLabel(),
                'scope' => $viewer->isCEO()
                    ? 'company'
                    : ($viewer->isHRD() ? 'human_resources' : ($viewer->isManager() ? 'division_team' : 'personal')),
            ],
            'visible_team' => $visibleUsers->map(fn (User $user) => [
                'name' => $user->name,
                'username' => $user->username,
                'job_title' => $user->job_title,
                'role' => $user->role,
                'division' => $user->divisionLabel(),
            ])->values(),
            'tasks' => Task::query()
                ->with(['user:id,name,username', 'kpi:id,title'])
                ->whereIn('user_id', $visibleIds)
                ->latest('updated_at')
                ->limit(40)
                ->get()
                ->map(fn (Task $task) => [
                    'owner' => $task->user?->username,
                    'title' => $task->title,
                    'status' => $task->status,
                    'deadline' => $task->deadline?->toIso8601String(),
                    'kpi' => $task->kpi?->title ?? $task->relation,
                    'metric_value' => $task->metric_value,
                ]),
            'attendance_today' => Attendance::query()
                ->with('user:id,name,username')
                ->whereIn('user_id', $visibleIds)
                ->whereDate('clock_in', now()->toDateString())
                ->latest('clock_in')
                ->get()
                ->map(fn (Attendance $attendance) => [
                    'username' => $attendance->user?->username,
                    'clock_in' => $attendance->clock_in?->toIso8601String(),
                    'clock_out' => $attendance->clock_out?->toIso8601String(),
                    'status' => $attendance->status,
                    'work_type' => $attendance->work_type,
                ]),
            'active_goals' => Goal::query()
                ->where('status', 'active')
                ->when(!$viewer->isCEO(), fn (Builder $query) => $query->where('division', $division))
                ->get(['id', 'title', 'description', 'division', 'year', 'progress']),
            'approved_kpi_plans' => KpiPlan::query()
                ->with(['goal:id,title,division,progress', 'manager:id,name,username', 'kpis:id,kpi_plan_id,title,target_value,unit,weight,current_value'])
                ->where('status', 'approved')
                ->when(!$viewer->isCEO(), fn (Builder $query) => $query->whereHas('goal', fn (Builder $goal) => $goal->where('division', $division)))
                ->get(),
            'pending_approvals' => $approvalQuery
                ->latest('submitted_at')
                ->limit(30)
                ->get()
                ->map(fn (ApprovalRequest $approval) => [
                    'type' => $approval->request_type,
                    'division' => $approval->division,
                    'status' => $approval->status,
                    'requester' => $approval->requester?->username,
                    'submitted_at' => $approval->submitted_at?->toIso8601String(),
                ]),
        ];

        if ($viewer->isCEO() || $division === 'marketing') {
            $context['marketing_pipeline'] = Lead::query()
                ->selectRaw('status, COUNT(*) as total, COALESCE(SUM(project_value), 0) as total_value')
                ->groupBy('status')
                ->get();
        }

        if ($channel) {
            $context['recent_channel_messages'] = ChatMessage::query()
                ->with('sender:id,name,username')
                ->where('channel', $channel)
                ->latest('id')
                ->limit(12)
                ->get()
                ->reverse()
                ->values()
                ->map(fn (ChatMessage $message) => [
                    'sender' => $message->type === 'ai_response' ? 'Suba-Arch Copilot' : $message->sender?->name,
                    'text' => $message->message,
                    'created_at' => $message->created_at?->toIso8601String(),
                ]);
        }

        return $context;
    }

    private function visibleUsers(User $viewer)
    {
        $query = User::query()->where('is_active', true);

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
}
