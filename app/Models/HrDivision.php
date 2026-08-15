<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrDivision extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'department_id',
        'code',
        'name',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function department()
    {
        return $this->belongsTo(HrDepartment::class, 'department_id');
    }

    public function positions()
    {
        return $this->hasMany(HrPosition::class, 'division_id');
    }
}
