<?php

declare(strict_types=1);

namespace Modules\Finance\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Modules\Finance\Models\ExchangeRate;

interface ExchangeRateRepositoryInterface
{
    public function all(): Collection;

    public function findById(string $id): ?ExchangeRate;

    public function create(array $data): ExchangeRate;

    public function update(string $id, array $data): bool;

    public function delete(string $id): bool;
}
