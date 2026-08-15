<?php

namespace Modules\Finance\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Modules\Finance\Models\CostCenter;

interface CostCenterRepositoryInterface
{
    public function all(): Collection;

    public function findById(string $id): ?CostCenter;

    public function create(array $data): CostCenter;

    public function update(string $id, array $data): bool;

    public function delete(string $id): bool;
}
