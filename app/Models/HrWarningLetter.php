<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrWarningLetter extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'issuer_id',
        'level',
        'reason',
        'effective_date',
        'valid_until',
        'status',
        'attachment_path',
    ];

    protected function casts(): array
    {
        return [
            'effective_date' => 'date',
            'valid_until' => 'date',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function issuer()
    {
        return $this->belongsTo(User::class, 'issuer_id');
    }
}
