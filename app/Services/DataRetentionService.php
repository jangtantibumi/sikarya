<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Lead;
use App\Models\LeaveRequest;
use App\Models\ProjectCost;
use App\Models\ResignationRequest;
use App\Models\RetentionRun;
use App\Models\TalentReview;
use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DataRetentionService
{
    private const DEFAULTS = [
        'retention.archive_inactive_days' => 30,
        'retention.anonymize_inactive_days' => 2555,
        'retention.auto_anonymize' => false,
        'retention.purge_soft_deleted_days' => 730,
        'retention.auto_purge' => false,
        'retention.storage_warning_mb' => 2048,
    ];

    private const SAFE_PURGE_MODELS = [
        Attendance::class => 'user_id',
        LeaveRequest::class => 'user_id',
        ResignationRequest::class => 'user_id',
        TalentReview::class => 'user_id',
        Lead::class => 'assigned_to',
        ProjectCost::class => 'created_by_id',
    ];

    public function __construct(
        private readonly SecuritySettingsService $settings,
        private readonly SecurityAuditService $audit,
    ) {}

    public function configuration(): array
    {
        $summary = $this->summary();
        $lastRun = RetentionRun::query()->latest('id')->first();

        return [
            'policy' => [
                'archive_inactive_days' => $this->integer('retention.archive_inactive_days'),
                'anonymize_inactive_days' => $this->integer('retention.anonymize_inactive_days'),
                'auto_anonymize' => $this->boolean('retention.auto_anonymize'),
                'purge_soft_deleted_days' => $this->integer('retention.purge_soft_deleted_days'),
                'auto_purge' => $this->boolean('retention.auto_purge'),
                'storage_warning_mb' => $this->integer('retention.storage_warning_mb'),
            ],
            'summary' => $summary,
            'last_run' => $lastRun ? [
                'mode' => $lastRun->mode,
                'metrics' => $lastRun->metrics,
                'completed_at' => $lastRun->completed_at?->toIso8601String(),
            ] : null,
        ];
    }

    public function update(array $values, User $actor): array
    {
        $map = [
            'retention.archive_inactive_days' => (int) $values['archive_inactive_days'],
            'retention.anonymize_inactive_days' => (int) $values['anonymize_inactive_days'],
            'retention.auto_anonymize' => (bool) $values['auto_anonymize'],
            'retention.purge_soft_deleted_days' => (int) $values['purge_soft_deleted_days'],
            'retention.auto_purge' => (bool) $values['auto_purge'],
            'retention.storage_warning_mb' => (int) $values['storage_warning_mb'],
        ];

        foreach ($map as $key => $value) {
            $this->settings->set($key, $value, $actor);
        }

        $this->audit->record(
            'retention.settings_updated',
            actor: $actor,
            metadata: $values,
            subjectType: 'data_retention',
            subjectId: 'global',
        );

        return $this->configuration();
    }

    public function run(?User $actor = null, string $mode = 'scheduled'): array
    {
        $metrics = DB::transaction(function (): array {
            $archived = User::query()
                ->where('is_active', false)
                ->whereNull('archived_at')
                ->whereNotNull('deactivated_at')
                ->where('deactivated_at', '<=', now()->subDays(
                    $this->integer('retention.archive_inactive_days'),
                ))
                ->update(['archived_at' => now()]);

            $anonymized = $this->boolean('retention.auto_anonymize')
                ? $this->anonymizeEligibleUsers()
                : 0;
            $purged = $this->boolean('retention.auto_purge')
                ? $this->purgeExpiredOperationalData()
                : 0;

            return compact('archived', 'anonymized', 'purged');
        });

        Cache::forget('erp.retention.storage-summary');
        $metrics['storage_bytes'] = $this->storageBytes();

        RetentionRun::query()->create([
            'mode' => $mode,
            'ran_by_id' => $actor?->id,
            'metrics' => $metrics,
            'completed_at' => now(),
        ]);

        $this->audit->record(
            'retention.run_completed',
            actor: $actor,
            metadata: ['mode' => $mode, ...$metrics],
            subjectType: 'data_retention',
            subjectId: 'global',
        );

        return $metrics;
    }

    public function summary(): array
    {
        $storageBytes = $this->storageBytes();
        $warningBytes = $this->integer('retention.storage_warning_mb') * 1024 * 1024;

        return [
            'active_users' => User::query()->where('is_active', true)->count(),
            'inactive_users' => User::query()->where('is_active', false)->count(),
            'archived_users' => User::query()->whereNotNull('archived_at')->count(),
            'legal_hold_users' => User::query()->where('legal_hold', true)->count(),
            'soft_deleted_records' => collect(array_keys(self::SAFE_PURGE_MODELS))
                ->sum(fn (string $model): int => in_array(SoftDeletes::class, class_uses_recursive($model), true)
                    ? $model::onlyTrashed()->count()
                    : 0),
            'storage_bytes' => $storageBytes,
            'storage_warning' => $storageBytes >= $warningBytes,
        ];
    }

    private function anonymizeEligibleUsers(): int
    {
        $count = 0;
        User::query()
            ->where('is_active', false)
            ->where('legal_hold', false)
            ->whereNull('anonymized_at')
            ->whereNotNull('deactivated_at')
            ->where('deactivated_at', '<=', now()->subDays(
                $this->integer('retention.anonymize_inactive_days'),
            ))
            ->orderBy('id')
            ->each(function (User $user) use (&$count): void {
                if ($user->signature_image_path) {
                    Storage::disk('local')->delete($user->signature_image_path);
                }

                $user->forceFill([
                    'name' => 'Arsip Pegawai '.$user->employee_code,
                    'email' => "archived-{$user->id}@invalid.local",
                    'job_title' => null,
                    'signature_image_path' => null,
                    'signature_consented_at' => null,
                    'anonymized_at' => now(),
                ])->save();
                $count++;
            });

        return $count;
    }

    private function purgeExpiredOperationalData(): int
    {
        $cutoff = now()->subDays($this->integer('retention.purge_soft_deleted_days'));
        $protectedIds = User::query()->where('legal_hold', true)->pluck('id');
        $purged = 0;

        foreach (self::SAFE_PURGE_MODELS as $model => $ownerColumn) {
            if (! in_array(SoftDeletes::class, class_uses_recursive($model), true)) {
                continue;
            }

            $query = $model::onlyTrashed()->where('deleted_at', '<=', $cutoff);
            if ($protectedIds->isNotEmpty()) {
                $query->whereNotIn($ownerColumn, $protectedIds);
            }

            $query->orderBy('id')->chunkById(100, function ($records) use (&$purged): void {
                foreach ($records as $record) {
                    if (method_exists($record, 'attachments')) {
                        $record->attachments()->get()->each(function ($attachment): void {
                            Storage::disk('local')->delete($attachment->stored_path);
                            $attachment->delete();
                        });
                    }
                    $record->forceDelete();
                    $purged++;
                }
            });
        }

        return $purged;
    }

    private function storageBytes(): int
    {
        return Cache::remember('erp.retention.storage-summary', now()->addMinutes(10), function (): int {
            return collect(Storage::disk('local')->allFiles())
                ->sum(fn (string $path): int => (int) Storage::disk('local')->size($path))
                + collect(Storage::disk('public')->allFiles())
                    ->sum(fn (string $path): int => (int) Storage::disk('public')->size($path));
        });
    }

    private function integer(string $key): int
    {
        return (int) $this->settings->get($key, self::DEFAULTS[$key]);
    }

    private function boolean(string $key): bool
    {
        return (bool) $this->settings->get($key, self::DEFAULTS[$key]);
    }
}
