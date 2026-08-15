<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'rest_start_time',
        'rest_end_time',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
