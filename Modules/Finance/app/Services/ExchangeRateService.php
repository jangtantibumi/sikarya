<?php

namespace Modules\Finance\Services;

use Illuminate\Database\Eloquent\Collection;
use Modules\Finance\Models\ExchangeRate;
use Modules\Finance\Repositories\Contracts\ExchangeRateRepositoryInterface;

class ExchangeRateService
{
    public function __construct(
        protected ExchangeRateRepositoryInterface $repository
    ) {}

    public function getAllRates(): Collection
    {
        return $this->repository->all();
    }

    public function getRateById(string $id): ?ExchangeRate
    {
        return $this->repository->findById($id);
    }

    public function createRate(array $data): ExchangeRate
    {
        return $this->repository->create($data);
    }

    public function updateRate(string $id, array $data): bool
    {
        return $this->repository->update($id, $data);
    }

    public function deleteRate(string $id): bool
    {
        return $this->repository->delete($id);
    }
}
