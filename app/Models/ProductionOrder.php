<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class ProductionOrder extends Model
{
    use BelongsToCompany;

    protected $fillable = ['company_id', 'number', 'bill_of_material_id', 'product_id', 'planned_quantity', 'completed_quantity', 'status', 'planned_date'];

    protected $casts = ['planned_quantity' => 'decimal:3', 'completed_quantity' => 'decimal:3', 'planned_date' => 'date'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function billOfMaterial()
    {
        return $this->belongsTo(BillOfMaterial::class);
    }

    public function materials()
    {
        return $this->hasMany(ProductionMaterial::class);
    }

    public function wastes()
    {
        return $this->hasMany(ProductionWaste::class);
    }

    public function qualityChecks()
    {
        return $this->hasMany(ProductionQualityCheck::class);
    }
}
