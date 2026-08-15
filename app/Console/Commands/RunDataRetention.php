<?php

namespace App\Console\Commands;

use App\Services\DataRetentionService;
use Illuminate\Console\Command;

class RunDataRetention extends Command
{
    protected $signature = 'erp:run-retention';

    protected $description = 'Archive and clean ERP data according to the CEO retention policy';

    public function handle(DataRetentionService $retention): int
    {
        $metrics = $retention->run(mode: 'scheduled');

        $this->info(sprintf(
            'Retention complete: %d archived, %d anonymized, %d purged.',
            $metrics['archived'],
            $metrics['anonymized'],
            $metrics['purged'],
        ));

        return self::SUCCESS;
    }
}
