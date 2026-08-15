<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrJobVacancy extends Model
{
    use HasFactory;

    protected $table = 'hr_job_vacancies';

    protected $fillable = [
        'company_id',
        'title',
        'department_id',
        'position_id',
        'description',
        'requirements',
        'quota',
        'status',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function department()
    {
        return $this->belongsTo(HrDepartment::class, 'department_id');
    }

    public function position()
    {
        return $this->belongsTo(HrPosition::class, 'position_id');
    }

    public function candidates()
    {
        return $this->hasMany(HrCandidate::class, 'job_vacancy_id');
    }
}
