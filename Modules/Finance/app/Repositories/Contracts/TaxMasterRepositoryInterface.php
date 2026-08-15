<?php

declare(strict_types=1);

namespace Modules\Finance\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Modules\Finance\Models\TaxMaster;

interface TaxMasterRepositoryInterface
{
    public function all(): Collection;

    public function findById(string $id): ?TaxMaster;

    public function create(array $data): TaxMaster;

    public function update(string $id, array $data): bool;

    public function delete(string $id): bool;
}
