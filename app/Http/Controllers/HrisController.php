<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Shift;
use App\Models\User;
use App\Models\Task;
use App\Models\LeaveRequest;
use App\Models\CompanyDocument;
use Illuminate\Support\Facades\Auth;

class HrisController extends Controller
{
    private function getCompanyId()
    {
        $user = Auth::user();
        if ($user->company_id) return $user->company_id;
        
        $membership = \App\Models\CompanyMembership::where('user_id', $user->id)->first();
        if ($membership) return $membership->company_id;
        
        return \App\Models\Company::first()->id;
    }

    // --- CEO / MASTER PORTAL METHODS ---

    public function manageShifts(Request $request)
    {
        $shifts = Shift::where('company_id', $this->getCompanyId())->get();
        // Typically returns a view or JSON for the modal
        return response()->json($shifts);
    }

    public function storeOvertimeType(Request $request)
    {
        if (!Auth::user()->isCEO()) abort(403);
        $request->validate([
            'name' => 'required|string',
            'rate_per_hour' => 'required|numeric'
        ]);

        $companyId = $this->getCompanyId();

        \App\Models\OvertimeType::create([
            'company_id' => $companyId,
            'name' => $request->name,
            'rate_per_hour' => $request->rate_per_hour,
        ]);

        return redirect()->back()->with('success', 'Jenis Lembur berhasil ditambahkan.');
    }

    public function updateOvertimeType(Request $request, $id)
    {
        if (!Auth::user()->isCEO()) abort(403);
        $request->validate([
            'name' => 'required|string',
            'rate_per_hour' => 'required|numeric'
        ]);

        $companyId = $this->getCompanyId();
        $ot = \App\Models\OvertimeType::where('company_id', $companyId)->findOrFail($id);
        $ot->update([
            'name' => $request->name,
            'rate_per_hour' => $request->rate_per_hour,
        ]);

        return redirect()->back()->with('success', 'Jenis Lembur berhasil diperbarui.');
    }

    public function destroyOvertimeType($id)
    {
        if (!Auth::user()->isCEO()) abort(403);
        
        $companyId = $this->getCompanyId();
        $ot = \App\Models\OvertimeType::where('company_id', $companyId)->findOrFail($id);
        $ot->delete();

        return redirect()->back()->with('success', 'Jenis Lembur berhasil dihapus.');
    }

    public function storeAttendanceSetting(Request $request)
    {
        if (!Auth::user()->isCEO()) abort(403);
        $request->validate([
            'name' => 'required|string',
            'rest_start_time' => 'required',
            'rest_end_time' => 'required'
        ]);

        $companyId = $this->getCompanyId();

        \App\Models\AttendanceSetting::create([
            'company_id' => $companyId,
            'name' => $request->name,
            'rest_start_time' => $request->rest_start_time,
            'rest_end_time' => $request->rest_end_time,
        ]);

        return redirect()->back()->with('success', 'Pengaturan waktu istirahat berhasil disimpan.');
    }

    public function updateAttendanceSetting(Request $request, $id)
    {
        if (!Auth::user()->isCEO()) abort(403);
        $request->validate([
            'name' => 'required|string',
            'rest_start_time' => 'required',
            'rest_end_time' => 'required'
        ]);

        $setting = \App\Models\AttendanceSetting::where('company_id', $this->getCompanyId())->findOrFail($id);
        $setting->update([
            'name' => $request->name,
            'rest_start_time' => $request->rest_start_time,
            'rest_end_time' => $request->rest_end_time,
        ]);

        return redirect()->back()->with('success', 'Pengaturan waktu istirahat berhasil diperbarui.');
    }

    public function destroyAttendanceSetting($id)
    {
        if (!Auth::user()->isCEO()) abort(403);
        $setting = \App\Models\AttendanceSetting::where('company_id', $this->getCompanyId())->findOrFail($id);
        $setting->delete();

        return redirect()->back()->with('success', 'Pengaturan waktu istirahat berhasil dihapus.');
    }

    public function downloadBackup()
    {
        if (!Auth::user()->isCEO()) abort(403);
        $companyId = Auth::user()->company_id;
        
        $attendances = \App\Models\Attendance::with('user')
            ->whereHas('user', function($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })->get();
            
        $csvData = "ID,Karyawan,Waktu Clock In,Waktu Clock Out,Status,Lokasi,Luar Jam Kerja\n";
        foreach ($attendances as $att) {
            $isOutOfHours = $att->is_out_of_hours ? 'Ya' : 'Tidak';
            $csvData .= "{$att->id},\"{$att->user->name}\",{$att->clock_in},{$att->clock_out},{$att->status},\"{$att->location_name}\",{$isOutOfHours}\n";
        }
        
        return response($csvData)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="backup_absensi_'.date('Ymd').'.csv"');
    }

    public function storeShift(Request $request)
    {
        if (!Auth::user()->isCEO()) abort(403);
        $request->validate([
            'name' => 'required|string',
            'start_time' => 'required',
            'end_time' => 'required',
            'late_tolerance_minutes' => 'nullable|integer|min:0'
        ]);

        $shift = new Shift();
        $shift->company_id = $this->getCompanyId();
        $shift->name = $request->name;
        $shift->start_time = $request->start_time;
        $shift->end_time = $request->end_time;
        $shift->late_tolerance_minutes = $request->late_tolerance_minutes ?? 15;
        $shift->created_by_id = Auth::id();
        $shift->save();

        return back()->with('success', 'Shift berhasil ditambahkan.');
    }

    public function updateShift(Request $request, $id)
    {
        if (!Auth::user()->isCEO()) abort(403);
        $request->validate([
            'name' => 'required|string',
            'start_time' => 'required',
            'end_time' => 'required',
            'late_tolerance_minutes' => 'nullable|integer|min:0'
        ]);

        $companyId = $this->getCompanyId();
        $shift = Shift::where('company_id', $companyId)->findOrFail($id);
        
        $shift->update([
            'name' => $request->name,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'late_tolerance_minutes' => $request->late_tolerance_minutes ?? 15,
        ]);

        return redirect()->back()->with('success', 'Shift berhasil diupdate.');
    }

    public function destroyShift($id)
    {
        if (!Auth::user()->isCEO()) abort(403);
        
        $companyId = $this->getCompanyId();
        $shift = Shift::where('company_id', $companyId)->findOrFail($id);
        $shift->delete();

        return redirect()->back()->with('success', 'Shift berhasil dihapus.');
    }

    public function storeHoliday(Request $request)
    {
        if (!Auth::user()->isCEO()) abort(403);
        $request->validate([
            'name' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date'
        ]);

        $holiday = new \App\Models\Holiday();
        $holiday->company_id = $this->getCompanyId();
        $holiday->name = $request->name;
        $holiday->start_date = $request->start_date;
        $holiday->end_date = $request->end_date ?: $request->start_date;
        $holiday->save();

        return redirect()->back()->with('success', 'Hari libur berhasil ditambahkan.');
    }

    public function destroyHoliday($id)
    {
        if (!Auth::user()->isCEO()) abort(403);
        
        $companyId = $this->getCompanyId();
        $holiday = \App\Models\Holiday::where('company_id', $companyId)->findOrFail($id);
        $holiday->delete();

        return redirect()->back()->with('success', 'Hari libur berhasil dihapus.');
    }

    public function manageOrgChart()
    {
        // Return hierarchy tree
        $companyId = Auth::user()->company_id;
        $users = User::where('company_id', $companyId)->get();
        
        $hierarchy = $this->buildTree($users);
        return response()->json($hierarchy);
    }

    private function buildTree($elements, $parentId = null) {
        $branch = array();
        foreach ($elements as $element) {
            if ($element->reports_to_id == $parentId) {
                $children = $this->buildTree($elements, $element->id);
                if ($children) {
                    $element->children = $children;
                }
                $branch[] = $element;
            }
        }
        return $branch;
    }

    public function storeEmployee(Request $request)
    {
        // Manager hiring flow: requires CEO approval
        $isCeo = Auth::user()->isCEO();
        $isApproved = $isCeo ? true : false;

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt('password123'),
            'company_id' => Auth::user()->company_id,
            'role' => $request->role ?? 'staff_ops',
            'job_title' => $request->job_title,
            'division' => $request->division ?? Auth::user()->divisionLabel(),
            'employment_type' => $request->employment_type ?? 'Full-Time',
            'reports_to_id' => $request->reports_to_id ?? Auth::id(),
            'default_shift_id' => $request->default_shift_id,
            'base_salary' => $request->base_salary ?? 0,
            'default_leave_quota' => $request->default_leave_quota ?? 12,
            'is_approved' => $isApproved
        ]);

        if (!$isApproved) {
            // Trigger notification to CEO
            // Notification::send($ceo, new PendingHireNotification($user));
            return redirect()->back()->with('success', 'Staf berhasil diusulkan. Menunggu ACC CEO.');
        }

        return redirect()->back()->with('success', 'Akun staf berhasil dibuat.');
    }

    public function approveEmployee($id)
    {
        if (!Auth::user()->isCEO()) abort(403);

        $user = User::findOrFail($id);
        $user->update(['is_approved' => true]);

        return redirect()->back()->with('success', 'Staf berhasil disetujui.');
    }

    public function uploadDocument(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'file' => 'required|file',
        ]);

        $path = $request->file('file')->store('company_documents', 'public');

        CompanyDocument::create([
            'company_id' => Auth::user()->company_id,
            'title' => $request->title,
            'file_path' => $path,
            'uploaded_by_id' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Dokumen berhasil diunggah.');
    }

    public function updateUser(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'name' => 'nullable|string',
            'email' => 'nullable|email',
            'base_salary' => 'nullable|numeric|min:0',
            'job_title' => 'nullable|string',
            'employment_type' => 'nullable|string',
            'role' => 'nullable|string',
            'target_hours_per_month' => 'nullable|numeric|min:0',
            'password' => 'nullable|string|min:6',
        ]);

        $userToEdit = User::findOrFail($request->user_id);
        $currentUser = Auth::user();

        if (!$currentUser->isCEO() && !$currentUser->isHRD() && !$currentUser->isManagerOf($userToEdit) && $currentUser->id != $userToEdit->id) {
            abort(403, 'Unauthorized action.');
        }
        
        $userToEdit->name = $request->name ?? $userToEdit->name;
        $userToEdit->email = $request->email ?? $userToEdit->email;
        if ($request->has('base_salary')) {
            $userToEdit->base_salary = $request->base_salary;
        }
        $userToEdit->job_title = $request->job_title ?? $userToEdit->job_title;
        $userToEdit->employment_type = $request->employment_type ?? $userToEdit->employment_type;
        
        if ($request->has('role')) {
            if (in_array($request->role, ['ceo', 'super_admin', 'platform_admin']) && !$currentUser->isCEO()) {
                // Ignore if a non-CEO tries to assign CEO/Admin role
            } else {
                $userToEdit->role = $request->role;
            }
        }
        
        if ($request->has('target_hours_per_month')) {
            $userToEdit->target_hours_per_month = $request->target_hours_per_month;
        }
        
        if (!empty($request->password)) {
            $userToEdit->password = bcrypt($request->password);
        }
        $userToEdit->save();

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Data profil karyawan berhasil diperbarui.']);
        }

        return redirect()->back()->with('success', 'Data profil / target kerja staf berhasil diperbarui.');
    }

    public function getTasksList(Request $request)
    {
        $companyId = Auth::user()->company_id;
        $tasks = Task::with('user')
            ->where('company_id', $companyId)
            ->latest()
            ->get()
            ->map(function ($task) {
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'type' => $task->type,
                    'status' => $task->status ?? 'pending',
                    'priority' => $task->priority ?? 'medium',
                    'deadline' => $task->deadline ? $task->deadline->format('Y-m-d') : '',
                    'deadline_formatted' => $task->deadline ? $task->deadline->format('d M Y') : 'N/A',
                    'user_id' => $task->user_id,
                    'user_name' => $task->user->name ?? 'Unknown',
                    'user_division' => $task->user->division ?? 'Tanpa Divisi',
                ];
            });

        return response()->json(['success' => true, 'tasks' => $tasks]);
    }

    public function storeTask(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'type' => 'required|string',
            'deadline' => 'nullable|date',
            'user_id' => 'nullable|exists:users,id',
            'parent_id' => 'nullable|exists:tasks,id',
            'priority' => 'nullable|string',
        ]);

        $userId = $request->user_id ?? Auth::id();

        // Idempotency / Anti Double Submit Lock (5 seconds)
        $lockKey = 'task_assign_lock_' . Auth::id() . '_' . md5($request->title . $userId);
        if (!\Illuminate\Support\Facades\Cache::add($lockKey, true, 5)) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tugas sudah diproses.',
                    // We don't have the task object here for the second request, but since it's a duplicate block, we can just return success true.
                    // The frontend will ignore missing task data or handle it gracefully.
                ]);
            }
            return redirect()->back();
        }

        $task = Task::create([
            'company_id' => Auth::user()->company_id,
            'user_id' => $userId,
            'parent_id' => $request->parent_id,
            'title' => $request->title,
            'type' => $request->type ?? 'goal',
            'status' => 'pending',
            'priority' => $request->priority ?? 'medium',
            'deadline' => $request->deadline,
        ]);

        // Load the assigned user so we can return it to frontend for instant SPA update
        $task->load('assignedUser');

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Tugas / Goal berhasil ditambahkan.',
                'task' => $task
            ]);
        }

        return redirect()->back()->with('success', 'Tugas / Goal berhasil ditambahkan.');
    }

    public function updateTask(Request $request, $id)
    {
        $task = Task::findOrFail($id);
        if ($task->company_id != Auth::user()->company_id) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
            abort(403);
        }

        $request->validate([
            'title' => 'required|string',
            'user_id' => 'required|exists:users,id',
            'deadline' => 'nullable|date',
            'status' => 'nullable|string',
            'priority' => 'nullable|string',
        ]);

        $task->update([
            'user_id' => $request->user_id,
            'title' => $request->title,
            'deadline' => $request->deadline,
            'status' => $request->status ?? $task->status,
            'priority' => $request->priority ?? $task->priority ?? 'medium',
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Tugas / Goal berhasil diperbarui.',
                'task' => $task
            ]);
        }

        return redirect()->back()->with('success', 'Tugas / Goal berhasil diperbarui.');
    }

    public function deleteTask(Request $request, $id)
    {
        $task = Task::find($id);
        if (!$task) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Tugas sudah terhapus atau tidak ditemukan.'], 404);
            }
            return redirect()->back()->with('error', 'Tugas sudah terhapus atau tidak ditemukan.');
        }
        
        if ($task->company_id != Auth::user()->company_id) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
            abort(403);
        }
        $task->delete();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Tugas / Goal berhasil dihapus.']);
        }

        return redirect()->back()->with('success', 'Tugas / Goal berhasil dihapus.');
    }

    public function uploadPayslip(Request $request)
    {
        if (!Auth::user()->isCEO() && !Auth::user()->isHRD()) abort(403);
        
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'month_year' => 'required|string',
            'net_salary' => 'required|numeric|min:0',
            'file' => 'required|file|mimes:pdf,jpg,png|max:5120',
        ]);

        $path = $request->file('file')->store('payslips', 'public');

        \App\Models\Payslip::create([
            'company_id' => Auth::user()->company_id,
            'user_id' => $request->user_id,
            'month_year' => $request->month_year,
            'net_salary' => $request->net_salary,
            'status' => 'issued',
            'file_path' => $path,
        ]);

        return redirect()->back()->with('success', 'Slip Gaji berhasil diupload.');
    }

    public function deletePayslip($id)
    {
        if (!Auth::user()->isCEO() && !Auth::user()->isHRD()) abort(403);
        
        $payslip = \App\Models\Payslip::findOrFail($id);
        
        if ($payslip->file_path) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($payslip->file_path);
        }
        
        $payslip->delete();

        return redirect()->back()->with('success', 'Slip Gaji berhasil dihapus.');
    }

    public function deleteDocument($id)
    {
        if (!Auth::user()->isCEO() && !Auth::user()->isHRD()) abort(403);
        $doc = \App\Models\CompanyDocument::findOrFail($id);
        if ($doc->company_id != Auth::user()->company_id) abort(403);
        
        if ($doc->file_path) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($doc->file_path);
        }
        $doc->delete();
        return redirect()->back()->with('success', 'Dokumen perusahaan berhasil dihapus.');
    }

    public function deleteEmployee(Request $request, $id)
    {
        if (!Auth::user()->isCEO() && !Auth::user()->isHRD()) abort(403);
        $employee = \App\Models\User::findOrFail($id);
        if ($employee->company_id != Auth::user()->company_id) abort(403);
        
        // Soft delete / Inactivate user
        $employee->is_approved = false;
        $employee->save();
        
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Karyawan berhasil dinonaktifkan.']);
        }
        return redirect()->back()->with('success', 'Karyawan berhasil dihapus/dinonaktifkan.');
    }

    public function deleteShift($id)
    {
        if (!Auth::user()->isCEO() && !Auth::user()->isHRD()) abort(403);
        $shift = \App\Models\Shift::findOrFail($id);
        if ($shift->company_id != Auth::user()->company_id) abort(403);
        
        $shift->delete();
        return redirect()->back()->with('success', 'Shift berhasil dihapus.');
    }

    // --- EMPLOYEE PORTAL METHODS ---

    public function cancelLeaveRequest($id)
    {
        $leave = LeaveRequest::findOrFail($id);
        
        if ($leave->user_id !== Auth::id()) abort(403);
        
        // Membutuhkan ACC atasan untuk membatalkan cuti yang sudah di-approve
        if ($leave->status === 'approved') {
            $leave->markAsCancellationRequested();
            return redirect()->back()->with('success', 'Pengajuan pembatalan cuti telah dikirim ke atasan.');
        } else {
            $leave->markAsRejected(); // Atau cancelled, jika status masih pending
            return redirect()->back()->with('success', 'Cuti berhasil dibatalkan.');
        }
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'password' => 'required|min:8'
        ]);

        $user = \App\Models\User::findOrFail($request->user_id);
        
        // Authorization check (only CEO or HR can reset password for others)
        if (!auth()->user()->isCEO() && auth()->user()->role !== 'hr') {
            abort(403, 'Unauthorized action.');
        }

        $user->password = \Illuminate\Support\Facades\Hash::make($request->password);
        $user->save();

        return redirect()->back()->with('success', 'Password pengguna berhasil direset.');
    }

    public function storeSalaryComponent(Request $request)
    {
        if (!auth()->user()->isCEO()) abort(403);
        $request->validate([
            'code' => 'required|string',
            'name' => 'required|string',
            'type' => 'required|in:allowance,deduction',
            'default_amount' => 'required|numeric'
        ]);

        \App\Models\HrSalaryComponent::create([
            'company_id' => $this->getCompanyId(),
            'code' => $request->code,
            'name' => $request->name,
            'type' => $request->type,
            'is_default' => $request->has('is_default'),
            'default_amount' => $request->default_amount,
        ]);

        return redirect()->back()->with('success', 'Komponen gaji berhasil ditambahkan.');
    }

    public function updateSalaryComponent(Request $request, $id)
    {
        if (!auth()->user()->isCEO()) abort(403);
        $request->validate([
            'name' => 'required|string',
            'type' => 'required|in:allowance,deduction',
            'default_amount' => 'required|numeric'
        ]);

        $comp = \App\Models\HrSalaryComponent::where('company_id', $this->getCompanyId())->findOrFail($id);
        $comp->update([
            'name' => $request->name,
            'type' => $request->type,
            'is_default' => $request->has('is_default'),
            'default_amount' => $request->default_amount,
        ]);

        return redirect()->back()->with('success', 'Komponen gaji berhasil diperbarui.');
    }

    public function destroySalaryComponent($id)
    {
        if (!auth()->user()->isCEO()) abort(403);
        $comp = \App\Models\HrSalaryComponent::where('company_id', $this->getCompanyId())->findOrFail($id);
        $comp->delete();

        return redirect()->back()->with('success', 'Komponen gaji berhasil dihapus.');
    }
}
