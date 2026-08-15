<?php

namespace App\Services;

use App\Models\Company;
use App\Models\CompanyFeature;
use Illuminate\Validation\ValidationException;

class CompanyFeatureManager
{
    public function catalogue(Company $company): array
    {
        return \Illuminate\Support\Facades\Cache::remember(
            "company.{$company->id}.features.catalogue",
            now()->addDay(),
            function () use ($company) {
                $features = $company->features()->with('division')->get()->keyBy('feature_key');

                return collect(config('master_modules', []))->map(function (array $definition, string $key) use ($features): array {
                    $feature = $features->get($key);
                    $state = $definition['permanent'] ?? false ? 'active' : ($feature->state ?? $definition['default']);
                    
                    // Gunakan nama divisi dinamis dari database jika ada, jika tidak fallback ke config
                    $groupName = $feature && $feature->division ? $feature->division->name : $definition['group'];
                    $divisionId = $feature && $feature->division ? $feature->division->id : null;
                    $divisionOrder = $feature && $feature->division ? $feature->division->order : 999;

                    return [
                        'key' => $key,
                        'label' => $definition['label'],
                        'group' => $groupName,
                        'division_id' => $divisionId,
                        'permanent' => (bool) ($definition['permanent'] ?? false),
                        'dependencies' => $definition['dependencies'] ?? [],
                        'state' => $state,
                        'division_order' => $divisionOrder,
                    ];
                })->sortBy('division_order')->values()->all();
            }
        );
    }

    public function set(Company $company, string $key, string $state): CompanyFeature
    {
        $definition = config("master_modules.{$key}");
        if (! is_array($definition)) {
            throw ValidationException::withMessages(['feature' => 'Modul tidak dikenal.']);
        }
        if (($definition['permanent'] ?? false) === true) {
            throw ValidationException::withMessages(['feature' => 'Modul fondasi tidak dapat dinonaktifkan.']);
        }
        if (! in_array($state, ['active', 'read_only', 'off', 'coming_soon'], true)) {
            throw ValidationException::withMessages(['state' => 'Status modul tidak valid.']);
        }
        if ($state === 'active') {
            $states = collect($this->catalogue($company))->pluck('state', 'key');
            $missing = collect($definition['dependencies'] ?? [])->filter(fn (string $dependency) => $states->get($dependency) !== 'active');
            if ($missing->isNotEmpty()) {
                throw ValidationException::withMessages(['feature' => 'Aktifkan modul prasyarat terlebih dahulu: '.$missing->implode(', ').'.']);
            }
        }

        $result = CompanyFeature::query()->updateOrCreate(
            ['company_id' => $company->id, 'feature_key' => $key],
            ['state' => $state],
        );

        \Illuminate\Support\Facades\Cache::forget("company.{$company->id}.features.catalogue");

        return $result;
    }
}
