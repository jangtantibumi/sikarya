<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class GoodsReceiptLine extends Model
{
    use BelongsToCompany;

    protected $fillable = ['company_id', 'goods_receipt_id', 'purchase_order_line_id', 'received_quantity'];
}
