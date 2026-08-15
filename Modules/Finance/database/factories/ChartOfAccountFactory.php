<?php

namespace Modules\Finance\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Finance\Models\ChartOfAccount;

class ChartOfAccountFactory extends Factory
{
    protected $model = ChartOfAccount::class;

    public function definition(): array
    {
        return [
            'company_id' => (string) Str::uuid(),
            'code' => (string) $this->faker->unique()->numberBetween(10000, 99999),
            'name' => $this->faker->words(3, true),
            'type' => 'asset',
            'balance_type' => 'debit',
            'is_header' => false,
            'is_reconciliation' => false,
            'is_active' => true,
        ];
    }
}
