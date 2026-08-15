<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrPosition extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'division_id',
        'job_grade_id',
        'code',
        'title',
        'description',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function division()
    {
        return $this->belongsTo(HrDivision::class, 'division_id');
    }

    public function jobGrade()
    {
        return $this->belongsTo(HrJobGrade::class, 'job_grade_id');
    }
}
