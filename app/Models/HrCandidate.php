<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrCandidate extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'job_vacancy_id',
        'full_name',
        'email',
        'phone',
        'resume_path',
        'status',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function jobVacancy()
    {
        return $this->belongsTo(HrJobVacancy::class, 'job_vacancy_id');
    }

    public function interviews()
    {
        return $this->hasMany(HrInterview::class, 'candidate_id');
    }
}
