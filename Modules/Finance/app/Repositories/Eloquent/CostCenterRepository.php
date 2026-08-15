<?php

declare(strict_types=1);

namespace Modules\Finance\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Collection;
use Modules\Finance\Models\CostCenter;
use Modules\Finance\Repositories\Contracts\CostCenterRepositoryInterface;

class CostCenterRepository implements CostCenterRepositoryInterface
{
    public function all(): Collection
    {
        return CostCenter::with('parent')->orderBy('code', 'asc')->get();
    }

    public function findById(string $id): ?CostCenter
    {
        return CostCenter::with(['parent', 'children'])->find($id);
    }

    public function create(array $data): CostCenter
    {
        return CostCenter::create($data);
    }

    public function update(string $id, array $data): bool
    {
        $cc = $this->findById($id);

        return $cc ? $cc->update($data) : false;
    }

    public function delete(string $id): bool
    {
        $cc = $this->findById($id);

        return $cc ? (bool) $cc->delete() : false;
    }
}
