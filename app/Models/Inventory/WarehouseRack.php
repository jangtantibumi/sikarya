<?php

declare(strict_types=1);

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WarehouseRack extends Model
{
    use HasFactory;

    protected $table = 'inv_warehouse_racks';

    protected $guarded = [];

    public function zone(): BelongsTo
    {
        return $this->belongsTo(WarehouseZone::class, 'zone_id');
    }

    public function bins(): HasMany
    {
        return $this->hasMany(WarehouseBin::class, 'rack_id');
    }
}
