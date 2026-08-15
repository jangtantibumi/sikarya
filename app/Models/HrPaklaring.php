<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrPaklaring extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'document_number',
        'start_date',
        'end_date',
        'last_position',
        'reason',
        'signed_by_id',
        'pdf_path',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function signer()
    {
        return $this->belongsTo(User::class, 'signed_by_id');
    }
}
