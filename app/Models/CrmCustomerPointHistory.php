<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrmCustomerPointHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'points',
        'description',
    ];

    public function customer()
    {
        return $this->belongsTo(CrmCustomer::class, 'customer_id');
    }
}
