<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrPerformanceReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'user_id',
        'reviewer_id',
        'review_period',
        'kpi_score',
        'okr_score',
        'task_score',
        'overall_score',
        'grade',
        'summary_notes',
        'status',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
