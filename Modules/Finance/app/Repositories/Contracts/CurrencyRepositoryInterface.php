<?php

declare(strict_types=1);

namespace Modules\Finance\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Modules\Finance\Models\Currency;

interface CurrencyRepositoryInterface
{
    public function all(): Collection;

    public function findById(string $id): ?Currency;

    public function create(array $data): Currency;

    public function update(string $id, array $data): bool;

    public function delete(string $id): bool;
}
