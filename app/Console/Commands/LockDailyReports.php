<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\DailyReport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class LockDailyReports extends Command
{
    protected $signature = 'hr:lock-daily-reports';

    protected $description = 'Locks daily reports and marks attendances as absent if no report is submitted within 24 hours';

    public function handle()
    {
        $now = now();
        // Get all attendances older than 24 hours from their clock_in date
        // where there is no daily report
        $cutoff = $now->copy()->subHours(24);

        $attendances = Attendance::query()
            ->where('clock_in', '<=', $cutoff)
            ->where('status', '!=', 'absent')
            ->whereDoesntHave('dailyReport')
            ->get();

        $count = 0;
        foreach ($attendances as $attendance) {
            $attendance->update([
                'status' => 'absent',
                'notes' => trim($attendance->notes.' (Absen otomatis: Tidak ada laporan harian dalam 24 jam)'),
            ]);
            $count++;
        }

        // Lock any submitted reports older than 24 hours so they can't be edited
        $reportsLocked = DailyReport::query()
            ->where('created_at', '<=', $cutoff)
            ->where('status', 'submitted')
            ->update(['status' => 'locked']);

        $this->info("Marked {$count} attendances as absent. Locked {$reportsLocked} reports.");
        Log::info("LockDailyReports run: {$count} attendances absent, {$reportsLocked} reports locked.");
    }
}
