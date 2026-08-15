<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Goal extends Model
{
    use BelongsToCompany, SoftDeletes;

    protected $fillable = [
        'title', 'description', 'division', 'year', 'status', 'progress', 'created_by',
    ];

    protected $casts = [
        'progress' => 'decimal:2',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function kpiPlans()
    {
        return $this->hasMany(KpiPlan::class);
    }

    public function attachments()
    {
        return $this->morphMany(RecordAttachment::class, 'attachable');
    }
}
