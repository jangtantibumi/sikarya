<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use BelongsToCompany;

    protected $fillable = ['company_id', 'supplier_id', 'number', 'status', 'order_date', 'expected_date', 'total_amount', 'created_by_id', 'approved_by_id', 'approved_at', 'rejected_reason', 'submitted_at'];

    public function lines()
    {
        return $this->hasMany(PurchaseOrderLine::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function receipts()
    {
        return $this->hasMany(GoodsReceipt::class);
    }
}
