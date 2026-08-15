<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrmCustomerTimeline extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'action',
        'description',
        'reference_id',
    ];

    public function customer()
    {
        return $this->belongsTo(CrmCustomer::class, 'customer_id');
    }
}
