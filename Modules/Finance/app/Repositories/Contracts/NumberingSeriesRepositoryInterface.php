<?php

namespace Modules\Finance\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Modules\Finance\Models\NumberingSeries;

interface NumberingSeriesRepositoryInterface
{
    public function all(): Collection;

    public function findById(string $id): ?NumberingSeries;

    public function findByModuleAndType(string $moduleCode, string $documentType): ?NumberingSeries;

    public function create(array $data): NumberingSeries;

    public function update(string $id, array $data): bool;

    public function delete(string $id): bool;
}
