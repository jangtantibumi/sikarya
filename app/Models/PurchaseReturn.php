<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class PurchaseReturn extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'goods_receipt_id',
        'number',
        'return_date',
        'status',
        'returned_by_id',
        'notes',
    ];

    public function goodsReceipt()
    {
        return $this->belongsTo(GoodsReceipt::class);
    }

    public function returnedBy()
    {
        return $this->belongsTo(User::class, 'returned_by_id');
    }

    public function lines()
    {
        return $this->hasMany(PurchaseReturnLine::class);
    }
}
