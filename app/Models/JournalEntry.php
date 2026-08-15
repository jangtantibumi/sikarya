<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JournalEntry extends Model
{
    protected $fillable = [
        'company_id',
        'source_type',
        'source_id',
        'entry_date',
        'reference',
        'description',
        'currency',
        'exchange_rate',
        'created_by_id',
        'posted_by_id',
        'posted_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
            'posted_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(JournalLine::class);
    }

    public function attachments()
    {
        return $this->morphMany(RecordAttachment::class, 'attachable');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function postedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by_id');
    }
}
