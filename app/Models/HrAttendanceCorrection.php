<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrAttendanceCorrection extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'user_id',
        'attendance_id',
        'date',
        'corrected_check_in',
        'corrected_check_out',
        'reason',
        'status',
        'approved_by',
        'approved_at',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
