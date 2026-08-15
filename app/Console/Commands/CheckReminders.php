<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\Product;
use App\Models\Task;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckReminders extends Command
{
    protected $signature = 'erp:check-reminders';

    protected $description = 'Periksa tenggat waktu Task dan stok menipis, kirim reminder';

    public function handle()
    {
        $this->info('Starting Universal Reminder Engine...');

        // 1. Cek Deadline Task (H-7, H-3, H-1)
        $tasks = Task::whereIn('status', ['pending', 'in_progress'])
            ->whereNotNull('due_date')
            ->get();

        $now = now();
        $taskAlerts = 0;

        foreach ($tasks as $task) {
            $daysLeft = $now->diffInDays($task->due_date, false);

            if (in_array($daysLeft, [7, 3, 1, 0])) {
                // Simulasi pengiriman notifikasi
                Log::info("REMINDER: Task #{$task->id} ({$task->title}) mendekati deadline dalam {$daysLeft} hari.");
                $taskAlerts++;
                // TODO: Kirim notifikasi sungguhan (Notification::send)
            }
        }

        // 2. Cek Stok Menipis
        $products = Product::where('status', 'active')->get();
        $stockAlerts = 0;

        foreach ($products as $product) {
            // Asumsikan ada kolom `stock` dan `minimum_stock`
            if (isset($product->minimum_stock) && $product->stock <= $product->minimum_stock) {
                Log::warning("REMINDER: Stok Produk #{$product->id} ({$product->name}) kritis. Sisa {$product->stock} <= {$product->minimum_stock}");
                $stockAlerts++;
            }
        }

        // 3. Cek Absensi H-1 (Lock 24 Jam Daily Report)
        $yesterday = $now->copy()->subDay()->toDateString();
        $missingReports = Attendance::where('date', $yesterday)
            ->where('status', 'present')
            ->whereDoesntHave('dailyReport')
            ->get();

        $absentAlerts = 0;
        foreach ($missingReports as $attendance) {
            // Kunci absensi jadi bolos karena lewat 24 jam tanpa laporan
            $attendance->update(['status' => 'absent']);
            Log::info("ABSENCE LOCK: User #{$attendance->user_id} tidak mengisi laporan untuk tgl {$yesterday}. Status diubah ke Bolos.");
            $absentAlerts++;
        }

        $this->info("Reminder selesai dijalankan. {$taskAlerts} task alerts, {$stockAlerts} stock alerts, {$absentAlerts} absence locks.");
    }
}
