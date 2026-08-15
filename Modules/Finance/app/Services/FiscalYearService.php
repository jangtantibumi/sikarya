<?php

namespace Modules\Finance\Services;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Modules\Finance\Models\FiscalPeriod;
use Modules\Finance\Models\FiscalYear;
use Modules\Finance\Repositories\Contracts\FiscalYearRepositoryInterface;

class FiscalYearService
{
    public function __construct(
        protected FiscalYearRepositoryInterface $repository
    ) {}

    public function getAllFiscalYears(): Collection
    {
        return $this->repository->all();
    }

    public function getFiscalYearById(string $id): ?FiscalYear
    {
        return $this->repository->findById($id);
    }

    public function createFiscalYearWithPeriods(array $data): FiscalYear
    {
        $fiscalYear = $this->repository->create($data);

        // Auto generate 12 monthly periods
        $startDate = Carbon::parse($fiscalYear->start_date);
        for ($i = 1; $i <= 12; $i++) {
            $periodStart = $startDate->copy()->addMonths($i - 1)->startOfMonth();
            $periodEnd = $periodStart->copy()->endOfMonth();

            FiscalPeriod::create([
                'company_id' => $fiscalYear->company_id,
                'fiscal_year_id' => $fiscalYear->id,
                'period_number' => $i,
                'name' => 'Period '.$i.' ('.$periodStart->format('M Y').')',
                'start_date' => $periodStart->toDateString(),
                'end_date' => $periodEnd->toDateString(),
                'status' => 'open',
            ]);
        }

        return $fiscalYear;
    }

    public function updateFiscalYear(string $id, array $data): bool
    {
        return $this->repository->update($id, $data);
    }

    public function deleteFiscalYear(string $id): bool
    {
        return $this->repository->delete($id);
    }
}
