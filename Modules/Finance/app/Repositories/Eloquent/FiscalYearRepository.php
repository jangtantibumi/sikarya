<?php

declare(strict_types=1);

namespace Modules\Finance\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Collection;
use Modules\Finance\Models\FiscalYear;
use Modules\Finance\Repositories\Contracts\FiscalYearRepositoryInterface;

class FiscalYearRepository implements FiscalYearRepositoryInterface
{
    public function all(): Collection
    {
        return FiscalYear::with('periods')->orderBy('start_date', 'desc')->get();
    }

    public function findById(string $id): ?FiscalYear
    {
        return FiscalYear::with('periods')->find($id);
    }

    public function create(array $data): FiscalYear
    {
        return FiscalYear::create($data);
    }

    public function update(string $id, array $data): bool
    {
        $fy = $this->findById($id);

        return $fy ? $fy->update($data) : false;
    }

    public function delete(string $id): bool
    {
        $fy = $this->findById($id);

        return $fy ? (bool) $fy->delete() : false;
    }
}
