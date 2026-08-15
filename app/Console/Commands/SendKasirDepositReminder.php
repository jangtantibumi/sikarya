<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\PosSale;
use App\Services\WorkflowNotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendKasirDepositReminder extends Command
{
    protected $signature = 'app:send-kasir-deposit-reminder';

    protected $description = 'Kirim reminder upload bukti setoran kasir yang pending lebih dari 2 hari.';

    public function handle(WorkflowNotificationService $notifications): int
    {
        $twoDaysAgo = Carbon::now(config('app.timezone', 'Asia/Jakarta'))->subDays(2);

        $pendingSales = PosSale::query()
            ->with('user')
            ->where(function ($q) {
                $q->whereNull('deposit_receipt')
                    ->orWhere('deposit_receipt', '');
            })
            ->where('created_at', '<=', $twoDaysAgo)
            ->get();

        $processed = 0;
        foreach ($pendingSales as $sale) {
            if ($sale->user) {
                try {
                    $notifications->send(
                        collect([$sale->user]),
                        'Reminder Setoran Kasir',
                        "Transaksi POS #{$sale->id} (Tgl: {$sale->created_at->format('d M Y')}) belum diunggah bukti setorannya.",
                        "pos_sale:{$sale->id}:deposit_reminder",
                        'kasir_reminder',
                        '/#pos-history',
                        ['pos_sale_id' => $sale->id]
                    );
                    $processed++;
                } catch (\Throwable $e) {
                    Log::warning('Gagal mengirim reminder setoran kasir: '.$e->getMessage());
                }
            }
        }

        $this->info("Reminder setoran kasir terkirim untuk {$processed} transaksi.");

        return self::SUCCESS;
    }
}
