<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrmCoupon extends Model
{
    use HasFactory;

    protected $table = 'crm_coupons';

    protected $fillable = [
        'coupon_code',
        'voucher_id',
        'customer_id',
        'discount_amount',
        'is_used',
        'used_at',
        'expires_at',
    ];

    protected $casts = [
        'discount_amount' => 'decimal:2',
        'is_used' => 'boolean',
        'used_at' => 'datetime',
        'expires_at' => 'date',
    ];

    public function voucher()
    {
        return $this->belongsTo(CrmVoucher::class, 'voucher_id');
    }

    public function customer()
    {
        return $this->belongsTo(CrmCustomer::class, 'customer_id');
    }
}
