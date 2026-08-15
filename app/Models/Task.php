<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, BelongsToCompany, SoftDeletes;

    protected $fillable = [
        'company_id',
        'parent_id',
        'user_id',
        'created_by_id',
        'kpi_id',
        'title',
        'type',
        'status',
        'priority',
        'deadline',
        'relation',
        'metric_value',
        'evidence',
        'feedback',
        'approved_at',
        'submitted_at',
        'verified_at',
    ];

    protected $casts = [
        'deadline' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function parent()
    {
        return $this->belongsTo(Task::class, 'parent_id');
    }

    public function subtasks()
    {
        return $this->hasMany(Task::class, 'parent_id');
    }

    public function kpi()
    {
        return $this->belongsTo(Kpi::class);
    }

    public function attachments()
    {
        return $this->morphMany(RecordAttachment::class, 'attachable');
    }

    public function markAsApproved(): void
    {
        $this->forceFill(['status' => 'in_progress'])->save();
    }

    public function approvalRequest()
    {
        return $this->morphOne(ApprovalRequest::class, 'subject');
    }
}
