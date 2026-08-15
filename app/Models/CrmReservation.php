<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmReservation extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'crm_reservations';

    protected $fillable = [
        'customer_id',
        'reservation_date',
        'reservation_time',
        'pax',
        'table_preference',
        'status',
        'special_requests',
    ];

    protected $casts = [
        'reservation_date' => 'date',
        'pax' => 'integer',
    ];

    public function customer()
    {
        return $this->belongsTo(CrmCustomer::class, 'customer_id');
    }
}
