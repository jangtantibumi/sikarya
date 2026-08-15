<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrPromotionRecommendation extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'user_id',
        'recommended_by',
        'target_position_id',
        'target_grade_id',
        'justification',
        'status',
        'approved_by',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function recommender()
    {
        return $this->belongsTo(User::class, 'recommended_by');
    }

    public function targetPosition()
    {
        return $this->belongsTo(HrPosition::class, 'target_position_id');
    }

    public function targetGrade()
    {
        return $this->belongsTo(HrJobGrade::class, 'target_grade_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
