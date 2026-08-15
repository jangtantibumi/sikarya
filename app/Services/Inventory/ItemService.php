<?php

declare(strict_types=1);

namespace App\Services\Inventory;

use App\Models\Inventory\Item;
use Illuminate\Pagination\LengthAwarePaginator;

class ItemService
{
    public function getPaginatedItems(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = Item::with(['category', 'brand', 'uom']);

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function createItem(array $data): Item
    {
        return Item::create($data);
    }

    public function updateItem(Item $item, array $data): Item
    {
        $item->update($data);

        return $item;
    }

    public function deleteItem(Item $item): bool
    {
        return $item->delete();
    }
}
