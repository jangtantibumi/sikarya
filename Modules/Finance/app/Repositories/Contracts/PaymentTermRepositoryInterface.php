<?php

namespace Modules\Finance\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Modules\Finance\Models\PaymentTerm;

interface PaymentTermRepositoryInterface
{
    public function all(): Collection;

    public function findById(string $id): ?PaymentTerm;

    public function create(array $data): PaymentTerm;

    public function update(string $id, array $data): bool;

    public function delete(string $id): bool;
}
