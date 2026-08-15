<?php

declare(strict_types=1);

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WarehouseZone extends Model
{
    use HasFactory;

    protected $table = 'inv_warehouse_zones';

    protected $guarded = [];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function racks(): HasMany
    {
        return $this->hasMany(WarehouseRack::class, 'zone_id');
    }
}
