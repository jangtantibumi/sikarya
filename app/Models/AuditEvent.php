<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditEvent extends Model
{
    use BelongsToCompany;

    public $timestamps = false;

    protected $fillable = [
        'actor_id',
        'event_type',
        'subject_type',
        'subject_id',
        'ip_address',
        'user_agent',
        'metadata',
        'previous_hash',
        'event_hash',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
