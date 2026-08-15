<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderLine extends Model
{
    use BelongsToCompany;

    protected $fillable = ['company_id', 'purchase_order_id', 'product_id', 'ordered_quantity', 'received_quantity', 'unit_price', 'line_total'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }
}
