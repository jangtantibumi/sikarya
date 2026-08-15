<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrmCustomerVoucher extends Model
{
    use HasFactory;

    protected $table = 'crm_customer_vouchers';

    protected $fillable = [
        'customer_id',
        'voucher_id',
        'is_used',
        'redeemed_at',
    ];

    protected $casts = [
        'is_used' => 'boolean',
        'redeemed_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(CrmCustomer::class, 'customer_id');
    }

    public function voucher()
    {
        return $this->belongsTo(CrmVoucher::class, 'voucher_id');
    }
}
