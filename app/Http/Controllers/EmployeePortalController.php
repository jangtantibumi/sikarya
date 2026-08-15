<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\AttendanceSetting;
use App\Models\Company;
use App\Models\OvertimeRequest;
use App\Models\OvertimeType;
use App\Models\Shift;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class EmployeePortalController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $company = $user->company ?? Company::first();

        // Get daily report for today if exists
        $todayReport = $user->dailyReports()->whereDate('date', today())->first();
        // Get active attendance (clocked in but no clock out)
        $activeAttendance = $user->attendances()->whereNull('clock_out')->whereDate('clock_in', today())->first();

        $shifts = Shift::where('company_id', $company->id)->get();
        $overtimeTypes = OvertimeType::where('company_id', $company->id)->get();
        $attendanceSettings = AttendanceSetting::where('company_id', $company->id)->get();
        $attendances = $user->attendances()->orderBy('clock_in', 'desc')->get();

        $targetTypes = ['all'];
        if ($user->isManager()) {
            $targetTypes[] = 'managers';
        }

        $latestAnnouncement = Announcement::where('company_id', $company->id)
            ->where('is_active', true)
            ->whereIn('target_type', $targetTypes)
            ->latest()
            ->first();

        $payrolls = $user->payrolls()->with('items')->orderBy('period_start', 'desc')->get();

        return view('employee-portal', [
            'user' => $user,
            'company' => $company,
            'todayReport' => $todayReport,
            'activeAttendance' => $activeAttendance,
            'leaveQuotas' => $user->leaveQuotas,
            'shifts' => $shifts,
            'overtimeTypes' => $overtimeTypes,
            'attendanceSettings' => $attendanceSettings,
            'attendances' => $attendances,
            'latestAnnouncement' => $latestAnnouncement,
            'payrolls' => $payrolls,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:8',
            'bio' => 'nullable|string|max:500',
            'profile_picture' => 'nullable|image|max:2048', // max 2MB
        ]);

        if ($request->hasFile('profile_picture')) {
            if ($user->profile_picture_path) {
                Storage::disk('public')->delete($user->profile_picture_path);
            }
            $path = $request->file('profile_picture')->store('profiles', 'public');
            $user->profile_picture_path = $path;
        }

        if ($request->filled('name')) {
            $user->name = $validated['name'];
        }

        if ($request->filled('password')) {
            $user->password = Hash::make($validated['password']);
        }

        if ($request->has('bio')) {
            $user->bio = $validated['bio'];
        }

        $user->save();

        return back()->with('status', 'Profile updated successfully!');
    }

    public function submitReport(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'task_id' => 'required',
            'content' => 'required|string',
            'attachment' => 'nullable|file|max:5120', // 5MB max
        ]);

        // Check if report already exists for today
        if ($user->dailyReports()->whereDate('date', today())->exists()) {
            return back()->withErrors(['content' => 'Report for today already submitted.']);
        }

        // Find today's attendance to link
        $attendance = $user->attendances()->whereDate('clock_in', today())->first();

        $path = null;
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('daily_reports', 'public');
        }

        // Simpan sebagai Daily Report
        $user->dailyReports()->create([
            'company_id' => $user->company_id,
            'attendance_id' => $attendance?->id,
            'date' => today(),
            'content' => $validated['content'],
            'attachment_path' => $path,
            'status' => 'submitted',
        ]);

        // Buat sebagai Sub-Task yang sudah selesai (Arsip Riwayat)
        if ($validated['task_id'] !== 'other') {
            Task::create([
                'company_id' => $user->company_id,
                'user_id' => $user->id,
                'parent_id' => $validated['task_id'], // Goal Utama
                'title' => $validated['content'], // Sub-task dari daily report
                'type' => 'daily',
                'status' => 'completed',
                'deadline' => today(),
            ]);
        }

        return back()->with('status', 'Laporan Harian dan Sub-Task berhasil disubmit!');
    }

    public function submitOvertimeRequest(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'overtime_type_id' => 'required|exists:overtime_types,id',
            'date' => 'required|date',
            'hours' => 'required|numeric|min:0.5',
        ]);

        OvertimeRequest::create([
            'user_id' => $user->id,
            'overtime_type_id' => $validated['overtime_type_id'],
            'date' => $validated['date'],
            'hours' => $validated['hours'],
            'status' => 'pending',
            'notes' => 'Pengajuan dari portal karyawan',
        ]);

        return back()->with('attendance_success', 'Pengajuan lembur berhasil dikirim.');
    }
}
