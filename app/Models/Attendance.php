<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attendance extends Model
{
    use BelongsToCompany, HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'shift_id',
        'clock_in',
        'clock_out',
        'rest_start',
        'rest_end',
        'status',
        'late_minutes',
        'location_coordinates',
        'location_name',
        'work_type',
        'is_holiday_work',
        'is_out_of_hours',
        'is_hidden',
    ];

    protected $casts = [
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
        'rest_start' => 'datetime',
        'rest_end' => 'datetime',
        'is_holiday_work' => 'boolean',
        'is_out_of_hours' => 'boolean',
        'is_hidden' => 'boolean',
    ];

    /**
     * Relationship: An attendance log belongs to a user.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: An attendance log belongs to a shift.
     */
    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function dailyReport()
    {
        return $this->hasOne(DailyReport::class);
    }

    /**
     * Calculate overtime hours with special logic for > 23:00
     */
    public function calculateOvertime(): float
    {
        if (! $this->clock_in || ! $this->clock_out) {
            return 0;
        }

        // Standard work hours: 08:00 to 17:00 (9 hours including break)
        // Overtime starts after 17:00.
        $overtimeHours = 0;

        $endOfDay = $this->clock_in->copy()->setTime(17, 0, 0);

        if ($this->clock_out->greaterThan($endOfDay)) {
            $diffInMinutes = $endOfDay->diffInMinutes($this->clock_out);
            $overtimeHours = $diffInMinutes / 60;

            // Special logic: Time after 23:00 gets 2x multiplier
            $lateNight = $this->clock_in->copy()->setTime(23, 0, 0);
            if ($this->clock_out->greaterThan($lateNight)) {
                $lateNightDiffInMinutes = $lateNight->diffInMinutes($this->clock_out);
                $lateNightHours = $lateNightDiffInMinutes / 60;
                // Add the late night hours again (so they are counted 2x total)
                $overtimeHours += $lateNightHours;
            }
        }

        return round($overtimeHours, 2);
    }
}
