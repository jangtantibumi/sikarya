<?php

namespace App\Services\Inventory;

use App\Models\Inventory\Warehouse;
use Illuminate\Pagination\LengthAwarePaginator;

class WarehouseService
{
    public function getPaginatedWarehouses(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = Warehouse::query();

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        return $query->latest()->paginate($perPage);
    }

    public function createWarehouse(array $data): Warehouse
    {
        return Warehouse::create($data);
    }

    public function updateWarehouse(Warehouse $warehouse, array $data): Warehouse
    {
        $warehouse->update($data);
        return $warehouse;
    }

    public function deleteWarehouse(Warehouse $warehouse): bool
    {
        return $warehouse->delete();
    }
}
