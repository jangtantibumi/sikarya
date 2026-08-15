<?php

namespace Modules\Finance\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Finance\Models\CostCenter;

class CostCenterFactory extends Factory
{
    protected $model = CostCenter::class;

    public function definition(): array
    {
        return [
            'company_id' => (string) Str::uuid(),
            'code' => 'CC-'.strtoupper($this->faker->unique()->bothify('??-###')),
            'name' => $this->faker->department().' Cost Center',
            'manager_name' => $this->faker->name(),
            'department' => $this->faker->word(),
            'is_active' => true,
        ];
    }
}
