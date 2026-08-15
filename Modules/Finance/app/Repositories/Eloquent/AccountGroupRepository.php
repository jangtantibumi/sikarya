<?php

declare(strict_types=1);

namespace Modules\Finance\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Collection;
use Modules\Finance\Models\AccountGroup;
use Modules\Finance\Repositories\Contracts\AccountGroupRepositoryInterface;

class AccountGroupRepository implements AccountGroupRepositoryInterface
{
    public function all(): Collection
    {
        return AccountGroup::orderBy('code', 'asc')->get();
    }

    public function findById(string $id): ?AccountGroup
    {
        return AccountGroup::find($id);
    }

    public function create(array $data): AccountGroup
    {
        return AccountGroup::create($data);
    }

    public function update(string $id, array $data): bool
    {
        $group = $this->findById($id);

        return $group ? $group->update($data) : false;
    }

    public function delete(string $id): bool
    {
        $group = $this->findById($id);

        return $group ? (bool) $group->delete() : false;
    }
}
