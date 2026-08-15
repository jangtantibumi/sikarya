<?php

namespace Modules\Finance\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Modules\Finance\Models\AccountGroup;

interface AccountGroupRepositoryInterface
{
    public function all(): Collection;

    public function findById(string $id): ?AccountGroup;

    public function create(array $data): AccountGroup;

    public function update(string $id, array $data): bool;

    public function delete(string $id): bool;
}
