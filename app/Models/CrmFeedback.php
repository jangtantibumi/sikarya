<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmFeedback extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'crm_feedbacks';

    protected $fillable = [
        'customer_id',
        'rating',
        'category',
        'message',
        'status',
        'resolution_notes',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    public function customer()
    {
        return $this->belongsTo(CrmCustomer::class, 'customer_id');
    }
}
