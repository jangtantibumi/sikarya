<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Throwable;

class ImportPortableErpData extends Command
{
    protected $signature = 'erp:data-import
        {path=storage/app/erp-portable-data.json : Input JSON path, relative to the project root or absolute}
        {--force : Required when APP_ENV is production}';

    protected $description = 'Replace business data using an integrity-checked portable ERP export.';

    public function handle(): int
    {
        if (app()->environment('production') && ! $this->option('force')) {
            $this->error('Tambahkan --force untuk import pada environment production.');

            return self::FAILURE;
        }

        $path = $this->absolutePath((string) $this->argument('path'));
        if (! File::isFile($path)) {
            $this->error("File import tidak ditemukan: {$path}");

            return self::FAILURE;
        }

        $payload = json_decode(File::get($path), true, flags: JSON_THROW_ON_ERROR);
        $this->validatePayload($payload);
        $tables = $payload['tables'];
        $driver = DB::getDriverName();

        $this->disableForeignKeys($driver);
        try {
            DB::transaction(function () use ($tables): void {
                foreach (array_reverse(ExportPortableErpData::TABLES) as $table) {
                    if (Schema::hasTable($table)) {
                        DB::table($table)->delete();
                    }
                }

                foreach (ExportPortableErpData::TABLES as $table) {
                    if (! Schema::hasTable($table) || ! isset($tables[$table])) {
                        continue;
                    }

                    foreach (array_chunk($tables[$table], 250) as $rows) {
                        if ($rows !== []) {
                            DB::table($table)->insert($rows);
                        }
                    }
                }
            }, 3);
        } catch (Throwable $exception) {
            throw new RuntimeException(
                'Import dibatalkan. Database tidak dapat menerima data portabel: '.$exception->getMessage(),
                previous: $exception,
            );
        } finally {
            $this->enableForeignKeys($driver);
        }

        Cache::flush();
        Artisan::call('view:clear');
        $rows = collect($tables)->sum(fn (array $items): int => count($items));
        $this->info("Import selesai: {$rows} baris dipulihkan.");
        $this->warn('Semua sesi lama sengaja tidak dipindahkan. Pengguna harus login kembali dengan OTP.');

        return self::SUCCESS;
    }

    private function validatePayload(array $payload): void
    {
        if (($payload['format'] ?? null) !== 'suba-arch-erp-portable-data'
            || (int) ($payload['version'] ?? 0) !== 1
            || ! is_array($payload['tables'] ?? null)) {
            throw new RuntimeException('Format file import tidak dikenali.');
        }

        $encodedTables = json_encode(
            $payload['tables'],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
        );
        if (! hash_equals((string) ($payload['integrity_sha256'] ?? ''), hash('sha256', $encodedTables))) {
            throw new RuntimeException('Pemeriksaan integritas gagal. File data berubah atau rusak.');
        }

        $expectedKey = hash('sha256', (string) config('app.key'));
        if (! hash_equals((string) ($payload['app_key_fingerprint'] ?? ''), $expectedKey)) {
            throw new RuntimeException(
                'APP_KEY server berbeda dari sumber. Import dihentikan agar SMTP, API key, dan tanda tangan terenkripsi tidak rusak.',
            );
        }
    }

    private function disableForeignKeys(string $driver): void
    {
        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
        } elseif ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys=OFF');
        }
    }

    private function enableForeignKeys(string $driver): void
    {
        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        } elseif ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys=ON');
        }
    }

    private function absolutePath(string $path): string
    {
        if (preg_match('/^(?:[A-Za-z]:[\\\\\\/]|[\\\\\\/])/', $path) === 1) {
            return $path;
        }

        return base_path($path);
    }
}
