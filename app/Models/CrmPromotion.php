<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrmPromotion extends Model
{
    use HasFactory;

    protected $table = 'crm_promotions';

    protected $fillable = [
        'title',
        'promo_code',
        'discount_type',
        'discount_value',
        'min_spend',
        'valid_from',
        'valid_until',
        'is_active',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'min_spend' => 'decimal:2',
        'valid_from' => 'date',
        'valid_until' => 'date',
        'is_active' => 'boolean',
    ];
}
