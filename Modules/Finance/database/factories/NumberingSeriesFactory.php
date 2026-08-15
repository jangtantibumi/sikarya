<?php

declare(strict_types=1);

namespace Modules\Finance\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Finance\Models\NumberingSeries;

class NumberingSeriesFactory extends Factory
{
    protected $model = NumberingSeries::class;

    public function definition(): array
    {
        $docType = $this->faker->unique()->word();

        return [
            'company_id' => (string) Str::uuid(),
            'module_code' => 'FINANCE',
            'document_type' => strtoupper($docType),
            'prefix' => strtoupper(substr($docType, 0, 3)).'-{YYYY}-',
            'suffix' => null,
            'length' => 5,
            'current_number' => 0,
            'reset_cycle' => 'yearly',
            'last_reset_date' => now()->toDateString(),
            'sample_number' => strtoupper(substr($docType, 0, 3)).'-'.date('Y').'-00001',
            'is_active' => true,
        ];
    }
}
