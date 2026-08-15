<?php

declare(strict_types=1);

namespace Modules\Automation\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CalculateBackflushCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'automation:calculate-backflush';

    /**
     * The console command description.
     */
    protected $description = 'Menghitung dan memotong stok bahan baku berdasarkan produksi (Backflush).';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Memulai proses kalkulasi backflush...');
        Log::info('Automation: Menjalankan kalkulasi backflush persediaan.');

        // TODO: Implementasi query pemotongan stok otomatis berdasarkan resep BOM
        $this->comment('Kalkulasi backflush selesai dieksekusi (Simulasi).');

        return Command::SUCCESS;
    }
}
