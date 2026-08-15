<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class ExportPortableErpData extends Command
{
    protected $signature = 'erp:data-export
        {path=storage/app/erp-portable-data.json : Output JSON path, relative to the project root or absolute}';

    protected $description = 'Export business data to a database-neutral, integrity-protected JSON file.';

    public const TABLES = [
        'users',
        'system_settings',
        'feature_flags',
        'goals',
        'kpi_plans',
        'kpis',
        'rules',
        'leads',
        'client_inflows',
        'projects',
        'accounts',
        'journal_entries',
        'journal_lines',
        'project_costs',
        'talent_reviews',
        'erp_documents',
        'document_signatures',
        'tasks',
        'attendances',
        'leave_requests',
        'resignation_requests',
        'team_requests',
        'data_deletion_requests',
        'approval_requests',
        'approval_steps',
        'chat_messages',
        'metric_snapshots',
        'notifications',
        'audit_events',
    ];

    public function handle(): int
    {
        $path = $this->absolutePath((string) $this->argument('path'));
        File::ensureDirectoryExists(dirname($path));

        $tables = [];
        foreach (self::TABLES as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            $tables[$table] = DB::table($table)
                ->orderBy($this->primaryOrderColumn($table))
                ->get()
                ->map(fn (object $row): array => (array) $row)
                ->all();
        }

        $encodedTables = json_encode(
            $tables,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
        );
        $payload = [
            'format' => 'suba-arch-erp-portable-data',
            'version' => 1,
            'exported_at' => now()->toIso8601String(),
            'source_driver' => DB::getDriverName(),
            'app_key_fingerprint' => hash('sha256', (string) config('app.key')),
            'integrity_sha256' => hash('sha256', $encodedTables),
            'tables' => $tables,
        ];
        $json = json_encode(
            $payload,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
        );

        if (File::put($path, $json, true) === false) {
            throw new RuntimeException("Tidak dapat menulis hasil export ke {$path}.");
        }

        $rows = collect($tables)->sum(fn (array $items): int => count($items));
        $this->info("Export selesai: {$rows} baris dari ".count($tables)." tabel.");
        $this->line($path);
        $this->line('SHA-256: '.hash_file('sha256', $path));

        return self::SUCCESS;
    }

    private function absolutePath(string $path): string
    {
        if (preg_match('/^(?:[A-Za-z]:[\\\\\\/]|[\\\\\\/])/', $path) === 1) {
            return $path;
        }

        return base_path($path);
    }

    private function primaryOrderColumn(string $table): string
    {
        return $table === 'notifications' ? 'created_at' : 'id';
    }
}
