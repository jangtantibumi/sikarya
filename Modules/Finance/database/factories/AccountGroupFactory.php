<?php

namespace Modules\Finance\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Finance\Models\AccountGroup;

class AccountGroupFactory extends Factory
{
    protected $model = AccountGroup::class;

    public function definition(): array
    {
        return [
            'company_id' => (string) Str::uuid(),
            'code' => (string) $this->faker->unique()->numberBetween(1000, 9000),
            'name' => $this->faker->words(2, true),
            'category' => $this->faker->randomElement(['asset', 'liability', 'equity', 'revenue', 'expense']),
            'code_from' => '1000',
            'code_to' => '1999',
            'report_type' => 'balance_sheet',
            'is_active' => true,
        ];
    }
}
