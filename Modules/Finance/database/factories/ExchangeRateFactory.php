<?php

namespace Modules\Finance\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Finance\Models\ExchangeRate;

class ExchangeRateFactory extends Factory
{
    protected $model = ExchangeRate::class;

    public function definition(): array
    {
        return [
            'company_id' => (string) Str::uuid(),
            'rate_date' => now()->toDateString(),
            'rate_type' => 'spot',
            'rate' => $this->faker->randomFloat(4, 1, 16000),
            'is_active' => true,
        ];
    }
}
