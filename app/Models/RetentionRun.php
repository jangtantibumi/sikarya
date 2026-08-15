<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RetentionRun extends Model
{
    protected $fillable = [
        'mode',
        'ran_by_id',
        'metrics',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'metrics' => 'array',
            'completed_at' => 'datetime',
        ];
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'ran_by_id');
    }
}
