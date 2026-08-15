<?php

declare(strict_types=1);

namespace Modules\Finance\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Modules\Finance\Models\FiscalYear;

interface FiscalYearRepositoryInterface
{
    public function all(): Collection;

    public function findById(string $id): ?FiscalYear;

    public function create(array $data): FiscalYear;

    public function update(string $id, array $data): bool;

    public function delete(string $id): bool;
}
