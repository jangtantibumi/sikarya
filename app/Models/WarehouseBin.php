<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseBin extends Model
{
    protected $fillable = [
        'warehouse_rack_id',
        'code',
        'name',
    ];

    public function rack()
    {
        return $this->belongsTo(WarehouseRack::class, 'warehouse_rack_id');
    }
}
