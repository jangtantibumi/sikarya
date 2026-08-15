<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseRack extends Model
{
    protected $fillable = [
        'warehouse_id',
        'code',
        'name',
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function bins()
    {
        return $this->hasMany(WarehouseBin::class);
    }
}
