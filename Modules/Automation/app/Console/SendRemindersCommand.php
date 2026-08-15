<?php

declare(strict_types=1);

namespace Modules\Automation\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendRemindersCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'automation:send-reminders';

    /**
     * The console command description.
     */
    protected $description = 'Mengirim notifikasi peringatan ke karyawan terkait tugas atau stok minimal.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Memeriksa reminder yang perlu dikirim...');
        Log::info('Automation: Memeriksa dan mengirim reminder sistem.');

        // TODO: Implementasi antrean pengiriman reminder (email/websocket)
        $this->comment('Reminder berhasil dikirim (Simulasi).');

        return Command::SUCCESS;
    }
}
