<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'requester_id',
        'target_user_id',
        'type',
        'details',
        'status',
        'ceo_approved_at',
    ];

    protected $casts = [
        'details' => 'array',
        'ceo_approved_at' => 'datetime',
    ];
}
