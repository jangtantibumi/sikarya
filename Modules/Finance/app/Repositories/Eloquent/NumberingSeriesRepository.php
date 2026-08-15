<?php

declare(strict_types=1);

namespace Modules\Finance\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Collection;
use Modules\Finance\Models\NumberingSeries;
use Modules\Finance\Repositories\Contracts\NumberingSeriesRepositoryInterface;

class NumberingSeriesRepository implements NumberingSeriesRepositoryInterface
{
    public function all(): Collection
    {
        return NumberingSeries::orderBy('module_code', 'asc')->orderBy('document_type', 'asc')->get();
    }

    public function findById(string $id): ?NumberingSeries
    {
        return NumberingSeries::find($id);
    }

    public function findByModuleAndType(string $moduleCode, string $documentType): ?NumberingSeries
    {
        return NumberingSeries::where('module_code', $moduleCode)
            ->where('document_type', $documentType)
            ->where('is_active', true)
            ->first();
    }

    public function create(array $data): NumberingSeries
    {
        return NumberingSeries::create($data);
    }

    public function update(string $id, array $data): bool
    {
        $ns = $this->findById($id);

        return $ns ? $ns->update($data) : false;
    }

    public function delete(string $id): bool
    {
        $ns = $this->findById($id);

        return $ns ? (bool) $ns->delete() : false;
    }
}
