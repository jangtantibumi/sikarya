<?php

namespace Modules\Finance\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Collection;
use Modules\Finance\Models\ExchangeRate;
use Modules\Finance\Repositories\Contracts\ExchangeRateRepositoryInterface;

class ExchangeRateRepository implements ExchangeRateRepositoryInterface
{
    public function all(): Collection
    {
        return ExchangeRate::with(['fromCurrency', 'toCurrency'])->orderBy('rate_date', 'desc')->get();
    }

    public function findById(string $id): ?ExchangeRate
    {
        return ExchangeRate::with(['fromCurrency', 'toCurrency'])->find($id);
    }

    public function create(array $data): ExchangeRate
    {
        return ExchangeRate::create($data);
    }

    public function update(string $id, array $data): bool
    {
        $rate = $this->findById($id);

        return $rate ? $rate->update($data) : false;
    }

    public function delete(string $id): bool
    {
        $rate = $this->findById($id);

        return $rate ? (bool) $rate->delete() : false;
    }
}
