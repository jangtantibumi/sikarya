<?php

declare(strict_types=1);

namespace Modules\Finance\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Finance\Models\PaymentTerm;

class PaymentTermFactory extends Factory
{
    protected $model = PaymentTerm::class;

    public function definition(): array
    {
        return [
            'company_id' => (string) Str::uuid(),
            'code' => 'NET'.$this->faker->unique()->numberBetween(10, 90),
            'name' => 'Net '.$this->faker->numberBetween(10, 90).' Days',
            'net_days' => $this->faker->numberBetween(10, 90),
            'discount_days' => 0,
            'discount_percentage' => 0.00,
            'description' => $this->faker->sentence(),
            'is_active' => true,
        ];
    }
}
