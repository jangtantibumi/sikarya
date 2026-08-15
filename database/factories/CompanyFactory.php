<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'slug' => $this->faker->unique()->slug(2),
            'industry' => $this->faker->randomElement(['technology', 'finance', 'retail', 'manufacturing']),
            'timezone' => 'Asia/Jakarta',
            'currency' => 'IDR',
            'status' => 'active',
            'branding' => ['primary_color' => '#059669'],
        ];
    }
}
