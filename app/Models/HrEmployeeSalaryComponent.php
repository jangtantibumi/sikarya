<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrEmployeeSalaryComponent extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'salary_component_id',
        'amount',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function salaryComponent()
    {
        return $this->belongsTo(HrSalaryComponent::class, 'salary_component_id');
    }
}
