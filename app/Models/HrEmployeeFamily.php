<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrEmployeeFamily extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'relationship',
        'gender',
        'birth_date',
        'phone',
        'is_dependent',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
