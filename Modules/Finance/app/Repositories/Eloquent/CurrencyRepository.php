<?php

declare(strict_types=1);

namespace Modules\Finance\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Collection;
use Modules\Finance\Models\Currency;
use Modules\Finance\Repositories\Contracts\CurrencyRepositoryInterface;

class CurrencyRepository implements CurrencyRepositoryInterface
{
    public function all(): Collection
    {
        return Currency::orderBy('code', 'asc')->get();
    }

    public function findById(string $id): ?Currency
    {
        return Currency::find($id);
    }

    public function create(array $data): Currency
    {
        return Currency::create($data);
    }

    public function update(string $id, array $data): bool
    {
        $currency = $this->findById($id);

        return $currency ? $currency->update($data) : false;
    }

    public function delete(string $id): bool
    {
        $currency = $this->findById($id);

        return $currency ? (bool) $currency->delete() : false;
    }
}
