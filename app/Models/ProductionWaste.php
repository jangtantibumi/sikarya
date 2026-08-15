<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class ProductionWaste extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'production_order_id',
        'product_id',
        'quantity',
        'type',
        'reason',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
    ];

    public function productionOrder()
    {
        return $this->belongsTo(ProductionOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
