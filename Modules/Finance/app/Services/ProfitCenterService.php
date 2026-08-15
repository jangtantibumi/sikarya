<?php

namespace Modules\Finance\Services;

use Illuminate\Database\Eloquent\Collection;
use Modules\Finance\Models\ProfitCenter;
use Modules\Finance\Repositories\Contracts\ProfitCenterRepositoryInterface;

class ProfitCenterService
{
    public function __construct(
        protected ProfitCenterRepositoryInterface $repository
    ) {}

    public function getAllProfitCenters(): Collection
    {
        return $this->repository->all();
    }

    public function getProfitCenterById(string $id): ?ProfitCenter
    {
        return $this->repository->findById($id);
    }

    public function createProfitCenter(array $data): ProfitCenter
    {
        return $this->repository->create($data);
    }

    public function updateProfitCenter(string $id, array $data): bool
    {
        return $this->repository->update($id, $data);
    }

    public function deleteProfitCenter(string $id): bool
    {
        return $this->repository->delete($id);
    }
}
