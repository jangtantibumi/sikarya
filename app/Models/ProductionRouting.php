<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class ProductionRouting extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'bill_of_material_id',
        'name'
    ];

    public function billOfMaterial()
    {
        return $this->belongsTo(BillOfMaterial::class);
    }

    public function steps()
    {
        return $this->hasMany(ProductionRoutingStep::class)->orderBy('sequence');
    }
}
