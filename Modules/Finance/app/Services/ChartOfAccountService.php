<?php

declare(strict_types=1);

namespace Modules\Finance\Services;

use Illuminate\Database\Eloquent\Collection;
use Modules\Finance\Models\ChartOfAccount;
use Modules\Finance\Repositories\Contracts\ChartOfAccountRepositoryInterface;

class ChartOfAccountService
{
    public function __construct(
        protected ChartOfAccountRepositoryInterface $repository
    ) {}

    public function getAllAccounts(): Collection
    {
        return $this->repository->all();
    }

    public function getAccountById(string $id): ?ChartOfAccount
    {
        return $this->repository->findById($id);
    }

    public function createAccount(array $data): ChartOfAccount
    {
        return $this->repository->create($data);
    }

    public function updateAccount(string $id, array $data): bool
    {
        return $this->repository->update($id, $data);
    }

    public function deleteAccount(string $id): bool
    {
        return $this->repository->delete($id);
    }
}
