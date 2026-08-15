<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class PurchaseReturnLine extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'purchase_return_id',
        'goods_receipt_line_id',
        'product_id',
        'return_quantity',
        'reason',
    ];

    public function purchaseReturn()
    {
        return $this->belongsTo(PurchaseReturn::class);
    }

    public function goodsReceiptLine()
    {
        return $this->belongsTo(GoodsReceiptLine::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
