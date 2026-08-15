<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\JournalEntry;
use App\Models\Project;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AccountingImportService
{
    private const HEADER_ALIASES = [
        'date' => ['date', 'tanggal', 'tanggal_transaksi'],
        'kind' => ['kind', 'jenis', 'jenis_transaksi', 'tipe'],
        'category' => ['category', 'kategori', 'akun', 'kategori_akun'],
        'amount' => ['amount', 'nilai', 'jumlah', 'nominal', 'nilai_rp'],
        'description' => ['description', 'keterangan', 'deskripsi', 'uraian'],
        'project_code' => ['project_code', 'kode_proyek', 'proyek', 'project'],
        'reference' => ['reference', 'referensi', 'nomor_referensi', 'no_referensi'],
    ];

    private const CATEGORY_ALIASES = [
        'design_revenue' => ['design_revenue', 'pendapatan jasa desain', 'pendapatan desain'],
        'contractor_revenue' => ['contractor_revenue', 'pendapatan kontraktor', 'pendapatan konstruksi'],
        'direct_project_cost' => ['direct_project_cost', 'biaya langsung proyek', 'biaya proyek'],
        'payroll_expense' => ['payroll_expense', 'beban gaji & sdm', 'beban gaji', 'gaji'],
        'operating_expense' => ['operating_expense', 'beban operasional', 'biaya operasional'],
        'marketing_expense' => ['marketing_expense', 'beban pemasaran', 'biaya pemasaran', 'marketing'],
    ];

    public function __construct(
        private readonly AccountingService $accounting,
    ) {}

    public function import(UploadedFile $file, User $actor): array
    {
        [$rows, $skipped] = $this->parseAndValidate($file);
        $batch = 'ACC-IMP-'.now()->format('Ymd-His').'-'.Str::upper(Str::random(4));

        $entries = DB::transaction(function () use ($rows, $actor, $batch) {
            return collect($rows)->map(function (array $row, int $index) use ($actor, $batch) {
                return $this->accounting->recordQuickTransaction(
                    $actor,
                    $row,
                    reference: $row['reference'] ?: "{$batch}-".str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
                    sourceType: 'accounting_file_import',
                );
            });
        });

        return [
            'batch' => $batch,
            'imported' => $entries->count(),
            'skipped' => $skipped,
            'total_amount' => round((float) $entries->sum(
                fn ($entry): float => (float) $entry->lines->sum('debit')
            ), 2),
            'entry_ids' => $entries->pluck('id')->all(),
        ];
    }

    private function parseAndValidate(UploadedFile $file): array
    {
        $path = $file->getRealPath();
        $handle = $path ? fopen($path, 'rb') : false;
        if (! $handle) {
            throw ValidationException::withMessages(['file' => 'File tidak dapat dibaca.']);
        }

        try {
            $firstLine = fgets($handle);
            if ($firstLine === false) {
                throw ValidationException::withMessages(['file' => 'File kosong.']);
            }
            $delimiter = $this->detectDelimiter($firstLine);
            rewind($handle);

            $rawHeaders = fgetcsv($handle, 0, $delimiter) ?: [];
            $headers = collect($rawHeaders)->map(fn ($value): string => $this->canonicalHeader($value))->all();
            $required = ['date', 'kind', 'category', 'amount', 'description'];
            $missing = array_values(array_diff($required, $headers));
            if ($missing !== []) {
                throw ValidationException::withMessages([
                    'file' => 'Kolom wajib belum lengkap: '.implode(', ', $missing).'. Gunakan template yang disediakan.',
                ]);
            }

            $rows = [];
            $errors = [];
            $skipped = 0;
            $lineNumber = 1;

            while (($values = fgetcsv($handle, 0, $delimiter)) !== false) {
                $lineNumber++;
                if ($lineNumber > 501) {
                    $errors[] = 'Maksimal 500 baris per proses impor.';
                    break;
                }
                if (collect($values)->every(fn ($value): bool => trim((string) $value) === '')) {
                    continue;
                }

                $raw = [];
                foreach ($headers as $index => $header) {
                    if ($header !== '') {
                        $raw[$header] = trim((string) ($values[$index] ?? ''));
                    }
                }

                try {
                    $row = $this->normalizeRow($raw, $lineNumber);
                    if ($row['reference'] && JournalEntry::query()->where('reference', $row['reference'])->exists()) {
                        $skipped++;

                        continue;
                    }
                    $rows[] = $row;
                } catch (\InvalidArgumentException $exception) {
                    $errors[] = $exception->getMessage();
                }
            }
        } finally {
            fclose($handle);
        }

        if ($errors !== []) {
            throw ValidationException::withMessages([
                'file' => 'Impor dibatalkan agar jurnal tetap akurat. '.implode(' ', array_slice($errors, 0, 8)),
            ]);
        }
        if ($rows === []) {
            throw ValidationException::withMessages([
                'file' => $skipped > 0
                    ? 'Semua baris sudah pernah diimpor berdasarkan nomor referensi.'
                    : 'Tidak ada baris transaksi yang dapat diimpor.',
            ]);
        }

        return [$rows, $skipped];
    }

    private function normalizeRow(array $raw, int $lineNumber): array
    {
        $date = $this->date($raw['date'] ?? '');
        $kind = $this->kind($raw['kind'] ?? '');
        $category = $this->category($raw['category'] ?? '');
        $amount = $this->amount($raw['amount'] ?? '');
        $description = trim((string) ($raw['description'] ?? ''));
        $reference = trim((string) ($raw['reference'] ?? ''));
        $projectCode = trim((string) ($raw['project_code'] ?? ''));

        if (! $date) {
            throw new \InvalidArgumentException("Baris {$lineNumber}: format tanggal tidak valid.");
        }
        if (! $kind) {
            throw new \InvalidArgumentException("Baris {$lineNumber}: jenis harus Pendapatan atau Biaya.");
        }
        if (! $category) {
            throw new \InvalidArgumentException("Baris {$lineNumber}: kategori tidak dikenali.");
        }
        if ($amount <= 0) {
            throw new \InvalidArgumentException("Baris {$lineNumber}: nominal harus lebih dari nol.");
        }
        if ($description === '' || mb_strlen($description) > 1000) {
            throw new \InvalidArgumentException("Baris {$lineNumber}: keterangan wajib diisi maksimal 1.000 karakter.");
        }
        if (($kind === 'revenue') !== str_ends_with($category, '_revenue')) {
            throw new \InvalidArgumentException("Baris {$lineNumber}: jenis dan kategori tidak sesuai.");
        }
        if (mb_strlen($reference) > 100) {
            throw new \InvalidArgumentException("Baris {$lineNumber}: referensi maksimal 100 karakter.");
        }

        $projectId = null;
        if ($projectCode !== '') {
            $projectId = Project::query()
                ->where('code', $projectCode)
                ->orWhere('name', $projectCode)
                ->value('id');
            if (! $projectId) {
                throw new \InvalidArgumentException("Baris {$lineNumber}: proyek {$projectCode} tidak ditemukan.");
            }
        }

        return [
            'date' => $date,
            'kind' => $kind,
            'category' => $category,
            'amount' => $amount,
            'description' => $description,
            'project_id' => $projectId,
            'reference' => $reference ?: null,
        ];
    }

    private function canonicalHeader(mixed $header): string
    {
        $normalized = $this->normalizeText((string) $header);
        foreach (self::HEADER_ALIASES as $canonical => $aliases) {
            if (in_array($normalized, array_map([$this, 'normalizeText'], $aliases), true)) {
                return $canonical;
            }
        }

        return '';
    }

    private function category(string $value): ?string
    {
        $normalized = $this->normalizeText($value);
        foreach (self::CATEGORY_ALIASES as $canonical => $aliases) {
            if (in_array($normalized, array_map([$this, 'normalizeText'], $aliases), true)) {
                return $canonical;
            }
        }

        return null;
    }

    private function kind(string $value): ?string
    {
        return match ($this->normalizeText($value)) {
            'revenue', 'pendapatan', 'pemasukan' => 'revenue',
            'expense', 'biaya', 'pengeluaran', 'beban' => 'expense',
            default => null,
        };
    }

    private function date(string $value): ?string
    {
        $value = trim($value);
        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', 'd.m.Y'] as $format) {
            try {
                $date = CarbonImmutable::createFromFormat("!{$format}", $value);
                if ($date && $date->format($format) === $value) {
                    return $date->format('Y-m-d');
                }
            } catch (\Throwable) {
                // Try the next supported format.
            }
        }

        return null;
    }

    private function amount(string $value): float
    {
        $normalized = preg_replace('/[^\d,.\-]/u', '', $value) ?? '';
        if (str_contains($normalized, ',') && str_contains($normalized, '.')) {
            $normalized = strrpos($normalized, ',') > strrpos($normalized, '.')
                ? str_replace(',', '.', str_replace('.', '', $normalized))
                : str_replace(',', '', $normalized);
        } elseif (substr_count($normalized, '.') > 1) {
            $normalized = str_replace('.', '', $normalized);
        } elseif (substr_count($normalized, ',') > 1) {
            $normalized = str_replace(',', '', $normalized);
        } elseif (str_contains($normalized, ',')) {
            $last = strlen($normalized) - strrpos($normalized, ',') - 1;
            $normalized = $last <= 2
                ? str_replace(',', '.', $normalized)
                : str_replace(',', '', $normalized);
        } elseif (str_contains($normalized, '.')) {
            $last = strlen($normalized) - strrpos($normalized, '.') - 1;
            if ($last === 3) {
                $normalized = str_replace('.', '', $normalized);
            }
        }

        return round((float) $normalized, 2);
    }

    private function detectDelimiter(string $line): string
    {
        $candidates = [',', ';', "\t"];

        return collect($candidates)
            ->sortByDesc(fn (string $delimiter): int => count(str_getcsv($line, $delimiter)))
            ->first() ?: ',';
    }

    private function normalizeText(string $value): string
    {
        return trim((string) preg_replace(
            '/_+/',
            '_',
            preg_replace('/[^a-z0-9]+/', '_', Str::lower(Str::ascii(trim($value)))) ?? '',
        ), '_');
    }
}
