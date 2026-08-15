<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class ProductionMaterial extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'production_order_id',
        'product_id',
        'planned_quantity',
        'issued_quantity',
        'actual_quantity',
        'status',
        'modified_by_id',
        'approved_by_id',
    ];

    protected $casts = [
        'planned_quantity' => 'decimal:3',
        'issued_quantity' => 'decimal:4',
        'actual_quantity' => 'decimal:3',
    ];

    public function productionOrder()
    {
        return $this->belongsTo(ProductionOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function modifiedBy()
    {
        return $this->belongsTo(User::class, 'modified_by_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by_id');
    }
}
