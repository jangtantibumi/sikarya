<?php

namespace Modules\Finance\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Modules\Finance\Models\ProfitCenter;

interface ProfitCenterRepositoryInterface
{
    public function all(): Collection;

    public function findById(string $id): ?ProfitCenter;

    public function create(array $data): ProfitCenter;

    public function update(string $id, array $data): bool;

    public function delete(string $id): bool;
}
