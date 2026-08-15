<?php

declare(strict_types=1);

namespace Modules\Finance\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Finance\Models\FiscalYear;

class FiscalYearFactory extends Factory
{
    protected $model = FiscalYear::class;

    public function definition(): array
    {
        $year = $this->faker->unique()->numberBetween(2020, 2030);

        return [
            'company_id' => (string) Str::uuid(),
            'code' => 'FY'.$year,
            'name' => 'Fiscal Year '.$year,
            'start_date' => $year.'-01-01',
            'end_date' => $year.'-12-31',
            'is_closed' => false,
            'is_active' => true,
        ];
    }
}
