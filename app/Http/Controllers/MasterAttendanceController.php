<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

class MasterAttendanceController extends Controller
{
    /**
     * Clock in for the authenticated user (simplified for master-demo portal).
     */
    public function clockIn(Request $request)
    {
        $request->validate([
            'shift_id' => 'nullable|exists:shifts,id',
            'location_coordinates' => 'nullable|string',
            'is_out_of_hours' => 'nullable|boolean'
        ]);

        $user = auth()->user();
        $timezone = config('app.timezone', 'Asia/Jakarta');
        $now = CarbonImmutable::now($timezone);

        $isOutOfHours = $request->input('is_out_of_hours', false);

        // Check if already clocked in today
        $existing = Attendance::query()
            ->where('user_id', $user->id)
            ->whereBetween('clock_in', [$now->startOfDay(), $now->endOfDay()])
            ->where('is_out_of_hours', $isOutOfHours)
            ->first();

        if ($existing) {
            return back()->with('attendance_error', 'Anda sudah clock in hari ini pada ' . $existing->clock_in->timezone($timezone)->format('H:i') . ' WIB.');
        }

        $status = 'Present';
        $shiftName = '-';

        $lateMinutes = 0;

        if ($request->shift_id) {
            $shift = \App\Models\Shift::findOrFail($request->shift_id);
            $shiftName = $shift->name;
            $shiftStartTime = CarbonImmutable::parse($shift->start_time, $timezone);
            $tolerance = $shift->late_tolerance_minutes ?? 15;
            
            $limitLateTime = $shiftStartTime->addMinutes($tolerance)->format('H:i');
            $currentTime = $now->format('H:i');
            
            if ($currentTime > $limitLateTime) {
                $status = 'Late';
                $lateMinutes = max(0, $now->diffInMinutes($shiftStartTime));
            }
        }

        Attendance::create([
            'user_id' => $user->id,
            'shift_id' => $request->shift_id,
            'clock_in' => $now,
            'status' => $status,
            'late_minutes' => $lateMinutes,
            'work_type' => $isOutOfHours ? 'out_of_hours' : 'WFO',
            'location_name' => 'Master Demo Portal',
            'location_coordinates' => $request->location_coordinates,
            'is_out_of_hours' => $isOutOfHours,
        ]);

        $typeLabel = $isOutOfHours ? 'Luar Jam Kerja' : 'Reguler';
        return back()->with('attendance_success', 'Clock in ' . $typeLabel . ' berhasil pada ' . $now->format('H:i') . ' WIB (Shift: ' . $shiftName . '). Status: ' . $status);
    }

    /**
     * Clock out for the authenticated user (simplified for master-demo portal).
     */
    public function clockOut(Request $request)
    {
        $user = auth()->user();
        $timezone = config('app.timezone', 'Asia/Jakarta');
        $now = CarbonImmutable::now($timezone);

        $attendance = Attendance::query()
            ->where('user_id', $user->id)
            ->whereBetween('clock_in', [$now->startOfDay(), $now->endOfDay()])
            ->whereNull('clock_out')
            ->latest('clock_in')
            ->first();

        if (!$attendance) {
            return back()->with('attendance_error', 'Anda belum clock in atau sudah clock out hari ini.');
        }

        if ($attendance->rest_start && !$attendance->rest_end) {
            return back()->with('attendance_error', 'Anda belum mengakhiri waktu istirahat! Harap tekan \'Selesai Istirahat\' sebelum melakukan Clock Out.');
        }

        $attendance->forceFill(['clock_out' => $now])->save();

        $duration = $attendance->clock_in->diffInMinutes($now);
        $hours = floor($duration / 60);
        $mins = $duration % 60;

        return back()->with('attendance_success', "Clock out berhasil pada {$now->format('H:i')} WIB. Durasi kerja: {$hours}j {$mins}m.");
    }

    public function restStart(Request $request)
    {
        $user = auth()->user();
        $timezone = config('app.timezone', 'Asia/Jakarta');
        $now = CarbonImmutable::now($timezone);

        $attendance = Attendance::query()
            ->where('user_id', $user->id)
            ->whereBetween('clock_in', [$now->startOfDay(), $now->endOfDay()])
            ->whereNull('clock_out')
            ->latest('clock_in')
            ->first();

        if (!$attendance) {
            return back()->with('attendance_error', 'Anda harus Clock In terlebih dahulu.');
        }

        $attendance->forceFill(['rest_start' => $now])->save();

        return back()->with('attendance_success', "Mulai istirahat pada {$now->format('H:i')} WIB.");
    }

    public function restEnd(Request $request)
    {
        $user = auth()->user();
        $timezone = config('app.timezone', 'Asia/Jakarta');
        $now = CarbonImmutable::now($timezone);

        $attendance = Attendance::query()
            ->where('user_id', $user->id)
            ->whereBetween('clock_in', [$now->startOfDay(), $now->endOfDay()])
            ->whereNull('clock_out')
            ->whereNotNull('rest_start')
            ->latest('clock_in')
            ->first();

        if (!$attendance) {
            return back()->with('attendance_error', 'Anda belum mulai istirahat.');
        }

        $attendance->forceFill(['rest_end' => $now])->save();

        return back()->with('attendance_success', "Selesai istirahat pada {$now->format('H:i')} WIB.");
    }

    public function submitOvertime(Request $request)
    {
        $request->validate([
            'overtime_type_id' => 'required|exists:overtime_types,id',
            'date' => 'required|date',
            'hours' => 'required|numeric|min:0.5',
        ]);

        \App\Models\OvertimeRequest::create([
            'user_id' => auth()->id(),
            'overtime_type_id' => $request->overtime_type_id,
            'date' => $request->date,
            'hours' => $request->hours,
            'status' => 'pending',
        ]);

        return back()->with('attendance_success', 'Pengajuan lembur berhasil dikirim.');
    }

    public function submitLeave(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string',
        ]);

        \App\Models\LeaveRequest::create([
            'user_id' => auth()->id(),
            'company_id' => auth()->user()->company_id,
            'type' => 'annual',
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        return back()->with('attendance_success', 'Pengajuan cuti tahunan berhasil dikirim.');
    }
}
