<?php

declare(strict_types=1);

namespace Modules\Finance\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Finance\Models\FiscalPeriod;

class FiscalPeriodFactory extends Factory
{
    protected $model = FiscalPeriod::class;

    public function definition(): array
    {
        return [
            'company_id' => (string) Str::uuid(),
            'period_number' => $this->faker->numberBetween(1, 12),
            'name' => 'Period '.$this->faker->monthName(),
            'start_date' => now()->startOfMonth()->toDateString(),
            'end_date' => now()->endOfMonth()->toDateString(),
            'status' => 'open',
        ];
    }
}
