<?php

declare(strict_types=1);
namespace Modules\Finance\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use App\Services\TenantContext;
use Modules\Finance\Models\TaxMaster;
use Modules\Finance\Repositories\Contracts\TaxMasterRepositoryInterface;

class TaxMasterService
{
    public function __construct(
        protected TaxMasterRepositoryInterface $repository,
        protected TenantContext $tenantContext
    ) {}

    public function getAllTaxes(): Collection
    {
        $tenantId = $this->tenantContext->id() ?? 'global';
        return Cache::rememberForever("tax_master_{$tenantId}", function () {
            return $this->repository->all();
        });
    }

    public function getTaxById(string $id): ?TaxMaster
    {
        return $this->repository->findById($id);
    }

    public function createTax(array $data): TaxMaster
    {
        $tax = $this->repository->create($data);
        $this->invalidateCache();
        return $tax;
    }

    public function updateTax(string $id, array $data): bool
    {
        $updated = $this->repository->update($id, $data);
        $this->invalidateCache();
        return $updated;
    }

    public function deleteTax(string $id): bool
    {
        $deleted = $this->repository->delete($id);
        $this->invalidateCache();
        return $deleted;
    }

    protected function invalidateCache(): void
    {
        $tenantId = $this->tenantContext->id() ?? 'global';
        Cache::forget("tax_master_{$tenantId}");
    }
}
