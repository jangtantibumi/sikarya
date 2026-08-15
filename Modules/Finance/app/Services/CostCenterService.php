<?php

namespace Modules\Finance\Services;

use Illuminate\Database\Eloquent\Collection;
use Modules\Finance\Models\CostCenter;
use Modules\Finance\Repositories\Contracts\CostCenterRepositoryInterface;

class CostCenterService
{
    public function __construct(
        protected CostCenterRepositoryInterface $repository
    ) {}

    public function getAllCostCenters(): Collection
    {
        return $this->repository->all();
    }

    public function getCostCenterById(string $id): ?CostCenter
    {
        return $this->repository->findById($id);
    }

    public function createCostCenter(array $data): CostCenter
    {
        return $this->repository->create($data);
    }

    public function updateCostCenter(string $id, array $data): bool
    {
        return $this->repository->update($id, $data);
    }

    public function deleteCostCenter(string $id): bool
    {
        return $this->repository->delete($id);
    }
}
