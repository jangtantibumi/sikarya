<?php

namespace Modules\Finance\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Collection;
use Modules\Finance\Models\PaymentTerm;
use Modules\Finance\Repositories\Contracts\PaymentTermRepositoryInterface;

class PaymentTermRepository implements PaymentTermRepositoryInterface
{
    public function all(): Collection
    {
        return PaymentTerm::orderBy('net_days', 'asc')->get();
    }

    public function findById(string $id): ?PaymentTerm
    {
        return PaymentTerm::find($id);
    }

    public function create(array $data): PaymentTerm
    {
        return PaymentTerm::create($data);
    }

    public function update(string $id, array $data): bool
    {
        $pt = $this->findById($id);

        return $pt ? $pt->update($data) : false;
    }

    public function delete(string $id): bool
    {
        $pt = $this->findById($id);

        return $pt ? (bool) $pt->delete() : false;
    }
}
