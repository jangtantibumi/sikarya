<?php

declare(strict_types=1);

namespace Modules\Finance\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Collection;
use Modules\Finance\Models\TaxMaster;
use Modules\Finance\Repositories\Contracts\TaxMasterRepositoryInterface;

class TaxMasterRepository implements TaxMasterRepositoryInterface
{
    public function all(): Collection
    {
        return TaxMaster::with('chartOfAccount')->orderBy('code', 'asc')->get();
    }

    public function findById(string $id): ?TaxMaster
    {
        return TaxMaster::with('chartOfAccount')->find($id);
    }

    public function create(array $data): TaxMaster
    {
        return TaxMaster::create($data);
    }

    public function update(string $id, array $data): bool
    {
        $tax = $this->findById($id);

        return $tax ? $tax->update($data) : false;
    }

    public function delete(string $id): bool
    {
        $tax = $this->findById($id);

        return $tax ? (bool) $tax->delete() : false;
    }
}
