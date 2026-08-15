<?php

namespace Modules\Finance\Services;

use Illuminate\Database\Eloquent\Collection;
use Modules\Finance\Models\Currency;
use Modules\Finance\Repositories\Contracts\CurrencyRepositoryInterface;

class CurrencyService
{
    public function __construct(
        protected CurrencyRepositoryInterface $repository
    ) {}

    public function getAllCurrencies(): Collection
    {
        return $this->repository->all();
    }

    public function getCurrencyById(string $id): ?Currency
    {
        return $this->repository->findById($id);
    }

    public function createCurrency(array $data): Currency
    {
        return $this->repository->create($data);
    }

    public function updateCurrency(string $id, array $data): bool
    {
        return $this->repository->update($id, $data);
    }

    public function deleteCurrency(string $id): bool
    {
        return $this->repository->delete($id);
    }
}
