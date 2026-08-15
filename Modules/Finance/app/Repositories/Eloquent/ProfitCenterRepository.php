<?php

declare(strict_types=1);

namespace Modules\Finance\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Collection;
use Modules\Finance\Models\ProfitCenter;
use Modules\Finance\Repositories\Contracts\ProfitCenterRepositoryInterface;

class ProfitCenterRepository implements ProfitCenterRepositoryInterface
{
    public function all(): Collection
    {
        return ProfitCenter::with('parent')->orderBy('code', 'asc')->get();
    }

    public function findById(string $id): ?ProfitCenter
    {
        return ProfitCenter::with(['parent', 'children'])->find($id);
    }

    public function create(array $data): ProfitCenter
    {
        return ProfitCenter::create($data);
    }

    public function update(string $id, array $data): bool
    {
        $pc = $this->findById($id);

        return $pc ? $pc->update($data) : false;
    }

    public function delete(string $id): bool
    {
        $pc = $this->findById($id);

        return $pc ? (bool) $pc->delete() : false;
    }
}
