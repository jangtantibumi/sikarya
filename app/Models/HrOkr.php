<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrOkr extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'user_id',
        'objective',
        'key_result',
        'target_value',
        'current_value',
        'weight',
        'status',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
