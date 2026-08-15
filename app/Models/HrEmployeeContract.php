<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrEmployeeContract extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'contract_number',
        'contract_type',
        'start_date',
        'end_date',
        'basic_salary',
        'status',
        'notes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
