<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrEmployeeEducation extends Model
{
    use HasFactory;

    protected $table = 'hr_employee_educations';

    protected $fillable = [
        'user_id',
        'degree',
        'institution',
        'major',
        'graduation_year',
        'gpa',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
