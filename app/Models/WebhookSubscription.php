<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebhookSubscription extends Model
{
    use BelongsToCompany, HasFactory;

    protected $fillable = [
        'company_id',
        'event_name',
        'url',
        'secret',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
