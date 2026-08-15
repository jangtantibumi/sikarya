<?php

declare(strict_types=1);

namespace Modules\Finance\Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;
use Modules\Finance\Models\FiscalYear;
use Modules\Finance\Services\FiscalYearService;

class FinanceFiscalYearSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();
        if (! $company) {
            return;
        }

        /** @var FiscalYearService $fyService */
        $fyService = app(FiscalYearService::class);

        // Check if FY2026 already exists
        $existing = FiscalYear::where('company_id', $company->id)
            ->where('code', 'FY2026')
            ->first();

        if (! $existing) {
            $fyService->createFiscalYearWithPeriods([
                'company_id' => $company->id,
                'code' => 'FY2026',
                'name' => 'Tahun Buku 2026',
                'start_date' => '2026-01-01',
                'end_date' => '2026-12-31',
                'is_closed' => false,
                'is_active' => true,
            ]);
        }
    }
}
