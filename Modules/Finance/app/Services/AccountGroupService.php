<?php

declare(strict_types=1);

namespace Modules\Finance\Services;

use Illuminate\Database\Eloquent\Collection;
use Modules\Finance\Models\AccountGroup;
use Modules\Finance\Repositories\Contracts\AccountGroupRepositoryInterface;

class AccountGroupService
{
    public function __construct(
        protected AccountGroupRepositoryInterface $repository
    ) {}

    public function getAllAccountGroups(): Collection
    {
        return $this->repository->all();
    }

    public function getAccountGroupById(string $id): ?AccountGroup
    {
        return $this->repository->findById($id);
    }

    public function createAccountGroup(array $data): AccountGroup
    {
        return $this->repository->create($data);
    }

    public function updateAccountGroup(string $id, array $data): bool
    {
        return $this->repository->update($id, $data);
    }

    public function deleteAccountGroup(string $id): bool
    {
        return $this->repository->delete($id);
    }
}
