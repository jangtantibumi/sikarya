<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TalentReview extends Model
{
    use BelongsToCompany, SoftDeletes;

    protected $fillable = [
        'user_id',
        'reviewer_id',
        'review_year',
        'review_cycle',
        'performance_score',
        'potential_score',
        'competency_score',
        'readiness',
        'status',
        'strengths',
        'development_plan',
        'next_role',
        'training_plan',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'review_year' => 'integer',
            'performance_score' => 'decimal:2',
            'potential_score' => 'decimal:2',
            'competency_score' => 'decimal:2',
            'training_plan' => 'array',
            'published_at' => 'datetime',
        ];
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
