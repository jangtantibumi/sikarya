<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrDepartment extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'manager_id',
        'parent_id',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function parent()
    {
        return $this->belongsTo(HrDepartment::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(HrDepartment::class, 'parent_id');
    }

    public function divisions()
    {
        return $this->hasMany(HrDivision::class, 'department_id');
    }
}
