<?php

declare(strict_types=1);

namespace Modules\Finance\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Finance\Models\ProfitCenter;

class ProfitCenterFactory extends Factory
{
    protected $model = ProfitCenter::class;

    public function definition(): array
    {
        return [
            'company_id' => (string) Str::uuid(),
            'code' => 'PC-'.strtoupper($this->faker->unique()->bothify('??-###')),
            'name' => $this->faker->company().' Profit Center',
            'manager_name' => $this->faker->name(),
            'segment' => $this->faker->word(),
            'is_active' => true,
        ];
    }
}
