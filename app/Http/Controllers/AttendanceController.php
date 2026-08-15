<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\ChatMessage;
use App\Models\User;
use App\Services\MetricAggregationService;
use App\Services\WorkflowNotificationService;
use Carbon\CarbonImmutable;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function __construct(
        private readonly MetricAggregationService $metrics,
        private readonly WorkflowNotificationService $notifications,
    ) {
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $query = Attendance::query()
            ->with('user')
            ->whereHas('user', fn ($users) => $users
                ->where('is_active', true)
                ->where('account_status', 'active'))
            ->orderByDesc('clock_in');

        if (!$user->isCEO() && !$user->isHRD()) {
            if ($user->isManager()) {
                $query->whereHas('user', function ($staff) use ($user): void {
                    $staff
                        ->where('is_active', true)
                        ->where('account_status', 'active')
                        ->where(function ($team) use ($user): void {
                            $team->where('id', $user->id)
                                ->orWhere('parent', $user->username);
                        });
                });
            } else {
                $query->where('user_id', $user->id);
            }
        }

        $timezone = config('app.timezone', 'Asia/Jakarta');
        $serverNow = CarbonImmutable::now($timezone);
        $records = $query->get()->map(function (Attendance $attendance) use ($timezone, $serverNow): array {
            [$lat, $lng] = array_pad(explode(',', (string) $attendance->location_coordinates, 2), 2, null);
            $clockIn = $attendance->clock_in?->copy()->timezone($timezone);
            $clockOut = $attendance->clock_out?->copy()->timezone($timezone);
            $durationEnd = $clockOut ?: $serverNow;
            $durationMinutes = $clockIn ? $clockIn->diffInMinutes($durationEnd) : 0;

            return [
                'id' => $attendance->id,
                'username' => $attendance->user?->username ?? 'unknown',
                'status' => $attendance->status,
                'time' => $clockIn ? $clockIn->format('H:i:s') . ' WIB' : '',
                'timeOut' => $clockOut ? $clockOut->format('H:i') . ' WIB' : '',
                'date' => $clockIn?->format('Y-m-d') ?? $attendance->created_at?->timezone($timezone)->format('Y-m-d'),
                'duration_hours' => round($durationMinutes / 60, 2),
                'clock_in_at' => $clockIn?->toIso8601String(),
                'clock_out_at' => $clockOut?->toIso8601String(),
                'is_active' => $clockIn !== null && $clockOut === null,
                'lat' => $lat !== null ? (float) $lat : null,
                'lng' => $lng !== null ? (float) $lng : null,
                'type' => $attendance->work_type ?? 'WFO',
                'location_name' => $attendance->location_name ?? 'Lokasi tidak tersedia',
            ];
        })->values();

        return response()->json([
            'server_time' => $serverNow->toIso8601String(),
            'records' => $records,
            'summary' => $this->monthlySummary($user, $serverNow),
        ]);
    }

    public function reverseGeocode(Request $request)
    {
        $validated = $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
        ]);

        return response()->json([
            'success' => true,
            'location_name' => $this->fetchAddress($validated['lat'], $validated['lng']),
        ]);
    }

    public function clockIn(Request $request)
    {
        $validated = $request->validate([
            'username' => ['nullable', 'string'],
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'type' => ['required', 'in:WFO,WFH'],
            'confirmed_holiday_work' => ['nullable', 'boolean'],
        ]);

        $user = $request->user();
        abort_if($user->isAlumni(), 403, 'Portal alumni tidak memiliki akses clock in.');
        if (!empty($validated['username']) && $validated['username'] !== $user->username) {
            abort(403, 'Absensi hanya dapat dilakukan untuk akun yang sedang masuk.');
        }

        $timezone = config('app.timezone', 'Asia/Jakarta');
        $serverNow = CarbonImmutable::now($timezone);
        $todayStart = $serverNow->startOfDay();
        $todayEnd = $serverNow->endOfDay();
        $holiday = $this->holidayFor($serverNow);

        if ($holiday['is_holiday'] && ! ($validated['confirmed_holiday_work'] ?? false)) {
            return response()->json([
                'error' => 'Hari ini terdaftar sebagai hari libur. Konfirmasi kerja pada hari libur diperlukan sebelum clock in.',
                'holiday_confirmation_required' => true,
                'holiday' => $holiday,
            ], 422);
        }

        $alreadyIn = Attendance::query()
            ->where('user_id', $user->id)
            ->whereBetween('clock_in', [$todayStart, $todayEnd])
            ->exists();

        if ($alreadyIn) {
            return response()->json(['error' => 'Anda sudah clock in hari ini.'], 422);
        }

        $status = $serverNow->format('H:i') > '09:00' ? 'Late' : 'Present';
        $locationName = $this->fetchAddress($validated['lat'], $validated['lng']);

        $attendance = Attendance::query()->create([
            'user_id' => $user->id,
            'clock_in' => $serverNow,
            'status' => $status,
            'location_coordinates' => $validated['lat'] . ',' . $validated['lng'],
            'work_type' => $validated['type'],
            'location_name' => $locationName,
            'is_holiday_work' => $holiday['is_holiday'],
        ]);

        $this->metrics->recalculateForDataSource('attendance', $user->divisionKey());
        $this->notifications->send(
            $user,
            'Clock in tercatat',
            "Clock in Anda tercatat pada {$serverNow->format('H:i')} WIB ({$status}).",
            "attendance:{$attendance->id}:clock_in",
            'attendance',
            '/#kpi-tasks',
            ['attendance_id' => $attendance->id],
        );

        return response()->json([
            'success' => true,
            'message' => 'Clock in berhasil dicatat berdasarkan waktu server.',
            'server_time' => $serverNow->toIso8601String(),
            'attendance_date' => $serverNow->format('Y-m-d'),
            'display_time' => $serverNow->format('H:i') . ' WIB',
            'attendance' => $attendance,
        ]);
    }

    public function clockOut(Request $request)
    {
        $validated = $request->validate([
            'username' => ['nullable', 'string'],
        ]);

        $user = $request->user();
        abort_if($user->isAlumni(), 403, 'Portal alumni tidak memiliki akses clock out.');
        if (!empty($validated['username']) && $validated['username'] !== $user->username) {
            abort(403, 'Absensi hanya dapat dilakukan untuk akun yang sedang masuk.');
        }

        $timezone = config('app.timezone', 'Asia/Jakarta');
        $serverNow = CarbonImmutable::now($timezone);

        $attendance = Attendance::query()
            ->where('user_id', $user->id)
            ->whereBetween('clock_in', [$serverNow->startOfDay(), $serverNow->endOfDay()])
            ->latest('clock_in')
            ->first();

        if (!$attendance) {
            return response()->json(['error' => 'Anda belum clock in hari ini.'], 422);
        }

        if ($attendance->clock_out) {
            return response()->json(['error' => 'Anda sudah clock out hari ini.'], 422);
        }

        $attendance->forceFill(['clock_out' => $serverNow])->save();

        $this->metrics->recalculateForDataSource('attendance', $user->divisionKey());
        $this->notifications->send(
            $user,
            'Clock out tercatat',
            "Clock out Anda tercatat pada {$serverNow->format('H:i')} WIB.",
            "attendance:{$attendance->id}:clock_out",
            'attendance',
            '/#kpi-tasks',
            ['attendance_id' => $attendance->id],
        );

        return response()->json([
            'success' => true,
            'message' => 'Clock out berhasil dicatat berdasarkan waktu server.',
            'server_time' => $serverNow->toIso8601String(),
            'attendance_date' => $serverNow->format('Y-m-d'),
            'display_time' => $serverNow->format('H:i') . ' WIB',
            'attendance' => $attendance->fresh(),
        ]);
    }

    private function fetchAddress(float $lat, float $lng): string
    {
        $fallback = "Koordinat: {$lat}, {$lng}";

        if (app()->environment('testing')) {
            return $fallback;
        }

        try {
            $response = (new Client())->get('https://nominatim.openstreetmap.org/reverse', [
                'query' => [
                    'format' => 'json',
                    'lat' => $lat,
                    'lon' => $lng,
                    'zoom' => 18,
                    'addressdetails' => 1,
                ],
                'headers' => [
                    'User-Agent' => 'SubaArchERP/1.0 (erp.suba-arch.co.id)',
                ],
                'timeout' => 4,
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            $address = $data['address'] ?? [];
            $parts = array_filter([
                $address['village'] ?? $address['suburb'] ?? $address['neighbourhood'] ?? null,
                $address['subdistrict'] ?? $address['county'] ?? null,
                $address['city'] ?? $address['regency'] ?? $address['state'] ?? null,
            ]);

            return $parts !== [] ? implode(', ', $parts) : ($data['display_name'] ?? $fallback);
        } catch (\Throwable) {
            return $fallback;
        }
    }

    private function monthlySummary(User $user, CarbonImmutable $now): array
    {
        $monthStart = $now->startOfMonth();
        $monthEnd = $now->endOfMonth();
        $holidayDates = $this->holidayDates($monthStart, $monthEnd);
        $workingDays = 0;
        for ($date = $monthStart; $date->lte($monthEnd); $date = $date->addDay()) {
            if (! $date->isWeekend() && ! isset($holidayDates[$date->toDateString()])) $workingDays++;
        }

        $minutes = Attendance::query()
            ->where('user_id', $user->id)
            ->whereBetween('clock_in', [$monthStart, $monthEnd])
            ->get()
            ->sum(fn (Attendance $attendance) => $attendance->clock_in
                ? $attendance->clock_in->diffInMinutes($attendance->clock_out ?: $now)
                : 0);
        $targetMinutes = $workingDays * 8 * 60;
        $holiday = $this->holidayFor($now);

        return [
            'month' => $now->format('Y-m'),
            'target_hours' => round($targetMinutes / 60, 2),
            'worked_hours' => round($minutes / 60, 2),
            'remaining_hours' => round(max(0, $targetMinutes - $minutes) / 60, 2),
            'shortfall_hours' => round(max(0, $targetMinutes - $minutes) / 60, 2),
            'working_days' => $workingDays,
            'holiday_dates' => array_keys($holidayDates),
            'today' => $holiday,
        ];
    }

    private function holidayFor(CarbonImmutable $date): array
    {
        $dateKey = $date->toDateString();
        $holidayDates = $this->holidayDates($date->startOfMonth(), $date->endOfMonth());
        $weekend = $date->isWeekend();
        return [
            'is_holiday' => $weekend || isset($holidayDates[$dateKey]),
            'label' => $holidayDates[$dateKey] ?? ($weekend ? 'Akhir pekan' : null),
        ];
    }

    private function holidayDates(CarbonImmutable $from, CarbonImmutable $to): array
    {
        return ChatMessage::query()
            ->where('type', 'holiday_announcement')
            ->get()
            ->flatMap(function (ChatMessage $message): array {
                $metadata = $message->metadata ?? [];
                if (empty($metadata['start_date']) || empty($metadata['end_date'])) return [];
                $start = CarbonImmutable::parse($metadata['start_date']);
                $end = CarbonImmutable::parse($metadata['end_date']);
                $dates = [];
                for ($date = $start; $date->lte($end); $date = $date->addDay()) {
                    $dates[$date->toDateString()] = $metadata['title'] ?? 'Hari libur';
                }
                return $dates;
            })
            ->filter(fn ($label, $date) => $date >= $from->toDateString() && $date <= $to->toDateString())
            ->all();
    }
}
