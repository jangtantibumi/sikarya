<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequestLine extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'purchase_request_id',
        'product_id',
        'quantity',
        'expected_date',
    ];

    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
