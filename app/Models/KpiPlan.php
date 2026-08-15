<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KpiPlan extends Model
{
    use BelongsToCompany, SoftDeletes;

    protected $fillable = [
        'goal_id', 'title', 'division', 'manager_id', 'status', 'score',
    ];

    protected $casts = [
        'score' => 'decimal:2',
    ];

    public function goal()
    {
        return $this->belongsTo(Goal::class);
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function kpis()
    {
        return $this->hasMany(Kpi::class);
    }

    public function attachments()
    {
        return $this->morphMany(RecordAttachment::class, 'attachable');
    }

    public function approvalRequest()
    {
        return $this->morphOne(ApprovalRequest::class, 'subject');
    }

    public function divisionKey(): ?string
    {
        return $this->division ?: $this->goal?->division;
    }

    public function displayTitle(): string
    {
        return $this->title
            ?: $this->goal?->title
            ?: "Rencana KPI #{$this->getKey()}";
    }

    public function markAsApproved(): void
    {
        $this->forceFill(['status' => 'approved'])->save();
    }

    public function markAsRejected(): void
    {
        $this->forceFill(['status' => 'rejected'])->save();
    }
}
