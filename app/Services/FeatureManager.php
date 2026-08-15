<?php

namespace App\Services;

use App\Models\FeatureFlag;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class FeatureManager
{
    private const CACHE_KEY = 'erp.feature-flags';

    public function enabled(string $key): bool
    {
        $definition = config("features.{$key}");
        if (!is_array($definition)) {
            return false;
        }

        if (($definition['locked'] ?? false) === true) {
            return true;
        }

        return (bool) ($this->states()[$key] ?? $definition['default'] ?? false);
    }

    public function states(): array
    {
        return Cache::remember(self::CACHE_KEY, now()->addMinutes(10), function (): array {
            $defaults = collect(config('features', []))
                ->mapWithKeys(fn (array $definition, string $key): array => [
                    $key => (bool) ($definition['default'] ?? false),
                ])
                ->all();

            if (!Schema::hasTable('feature_flags')) {
                return $defaults;
            }

            return array_replace(
                $defaults,
                FeatureFlag::query()->pluck('is_enabled', 'key')->map(fn ($value): bool => (bool) $value)->all(),
            );
        });
    }

    public function catalogue(): array
    {
        $states = $this->states();

        return collect(config('features', []))
            ->map(function (array $definition, string $key) use ($states): array {
                $available = (bool) ($definition['available'] ?? true);
                $locked = (bool) ($definition['locked'] ?? false);

                return [
                    'key' => $key,
                    'label' => $definition['label'] ?? $key,
                    'description' => $definition['description'] ?? '',
                    'category' => $definition['category'] ?? 'Lainnya',
                    'enabled' => $locked || ($available && (bool) ($states[$key] ?? false)),
                    'available' => $available,
                    'locked' => $locked,
                ];
            })
            ->values()
            ->all();
    }

    public function set(string $key, bool $enabled, User $actor): array
    {
        $definition = config("features.{$key}");
        if (!is_array($definition)) {
            throw ValidationException::withMessages([
                'feature' => 'Modul tidak dikenal.',
            ]);
        }

        if (($definition['locked'] ?? false) === true) {
            throw ValidationException::withMessages([
                'feature' => 'Modul fondasi ini tidak dapat dinonaktifkan.',
            ]);
        }

        if ($enabled && !($definition['available'] ?? true)) {
            throw ValidationException::withMessages([
                'feature' => 'Modul ini masih berada dalam roadmap dan belum dapat diaktifkan.',
            ]);
        }

        FeatureFlag::query()->updateOrCreate(
            ['key' => $key],
            [
                'is_enabled' => $enabled,
                'updated_by' => $actor->id,
            ],
        );

        Cache::forget(self::CACHE_KEY);

        return collect($this->catalogue())->firstWhere('key', $key) ?? [];
    }
}
