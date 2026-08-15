<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrEmployeeDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'document_type',
        'document_name',
        'file_path',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
