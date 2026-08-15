<?php

declare(strict_types=1);
namespace Modules\Finance\Services;

use Illuminate\Database\Eloquent\Collection;
use Modules\Finance\Models\PaymentTerm;
use Modules\Finance\Repositories\Contracts\PaymentTermRepositoryInterface;

class PaymentTermService
{
    public function __construct(
        protected PaymentTermRepositoryInterface $repository
    ) {}

    public function getAllPaymentTerms(): Collection
    {
        return $this->repository->all();
    }

    public function getPaymentTermById(string $id): ?PaymentTerm
    {
        return $this->repository->findById($id);
    }

    public function createPaymentTerm(array $data): PaymentTerm
    {
        return $this->repository->create($data);
    }

    public function updatePaymentTerm(string $id, array $data): bool
    {
        return $this->repository->update($id, $data);
    }

    public function deletePaymentTerm(string $id): bool
    {
        return $this->repository->delete($id);
    }
}
