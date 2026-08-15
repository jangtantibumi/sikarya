<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrJobGrade extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'level',
        'min_salary',
        'max_salary',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function positions()
    {
        return $this->hasMany(HrPosition::class, 'job_grade_id');
    }
}
