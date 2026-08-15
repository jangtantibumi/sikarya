<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryAuditLine extends Model
{
    protected $fillable = [
        'inventory_audit_id',
        'product_id',
        'expected_qty',
        'actual_qty',
        'difference',
        'reason',
    ];

    protected $casts = [
        'expected_qty' => 'decimal:3',
        'actual_qty' => 'decimal:3',
        'difference' => 'decimal:3',
    ];

    public function audit()
    {
        return $this->belongsTo(InventoryAudit::class, 'inventory_audit_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
