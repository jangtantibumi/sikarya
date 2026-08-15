<?php

namespace Modules\Finance\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Finance\Models\TaxMaster;

class TaxMasterFactory extends Factory
{
    protected $model = TaxMaster::class;

    public function definition(): array
    {
        return [
            'company_id' => (string) Str::uuid(),
            'code' => 'TAX-'.strtoupper($this->faker->unique()->lexify('???')),
            'name' => $this->faker->words(2, true).' Tax',
            'rate' => $this->faker->randomFloat(2, 1, 20),
            'tax_type' => 'vat',
            'calculation_type' => 'exclusive',
            'is_active' => true,
        ];
    }
}
