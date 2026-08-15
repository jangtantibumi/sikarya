<?php

declare(strict_types=1);

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    use HasFactory;

    protected $table = 'inv_warehouses';

    protected $guarded = [];

    public function zones(): HasMany
    {
        return $this->hasMany(WarehouseZone::class, 'warehouse_id');
    }

    public function stockSummaries(): HasMany
    {
        return $this->hasMany(StockSummary::class, 'warehouse_id');
    }
}
