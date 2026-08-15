<?php

namespace Modules\Finance\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Finance\Models\Currency;

class CurrencyFactory extends Factory
{
    protected $model = Currency::class;

    public function definition(): array
    {
        return [
            'company_id' => (string) Str::uuid(),
            'code' => strtoupper($this->faker->unique()->currencyCode()),
            'name' => $this->faker->currencyCode().' Currency',
            'symbol' => '$',
            'decimal_places' => 2,
            'is_base' => false,
            'is_active' => true,
        ];
    }
}
