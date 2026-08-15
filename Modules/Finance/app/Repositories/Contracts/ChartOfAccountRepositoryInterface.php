<?php

namespace Modules\Finance\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Modules\Finance\Models\ChartOfAccount;

interface ChartOfAccountRepositoryInterface
{
    public function all(): Collection;

    public function findById(string $id): ?ChartOfAccount;

    public function create(array $data): ChartOfAccount;

    public function update(string $id, array $data): bool;

    public function delete(string $id): bool;
}
