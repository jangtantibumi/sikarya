<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BomLine extends Model
{
    protected $fillable = [
        'bill_of_material_id',
        'component_id',
        'quantity_per_unit',
    ];

    protected $casts = [
        'quantity_per_unit' => 'decimal:3',
    ];

    public function billOfMaterial()
    {
        return $this->belongsTo(BillOfMaterial::class);
    }

    public function component()
    {
        return $this->belongsTo(Product::class, 'component_id');
    }
}
