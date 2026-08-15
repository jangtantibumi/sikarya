<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\ResignationRequest;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class EmployeeDataExportService
{
    public function archive(User $user): string
    {
        $filename = 'employee-separations/' . now()->format('Y/m') . "/{$user->employee_code}-{$user->id}-" . now()->format('YmdHis') . '.json';
        $payload = [
            'exported_at' => now()->toIso8601String(),
            'employee' => $user->only(['id', 'name', 'username', 'email', 'employee_code', 'role', 'job_title', 'employment_type', 'parent']),
            'attendance' => Attendance::query()->where('user_id', $user->id)->withTrashed()->get(),
            'tasks' => Task::query()->where('user_id', $user->id)->with(['kpi:id,title', 'attachments'])->withTrashed()->get(),
            'leave_requests' => LeaveRequest::query()->where('user_id', $user->id)->get(),
            'resignation_requests' => ResignationRequest::query()->where('user_id', $user->id)->get(),
        ];
        Storage::disk('local')->put($filename, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $filename;
    }
}
