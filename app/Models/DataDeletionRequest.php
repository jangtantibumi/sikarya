<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataDeletionRequest extends Model
{
    protected $fillable = [
        'resource_type',
        'target_type',
        'target_id',
        'target_label',
        'deletion_mode',
        'scope',
        'division',
        'requested_by_id',
        'reason',
        'status',
        'snapshot',
        'executed_by_id',
        'executed_at',
    ];

    protected $hidden = [
        'snapshot',
        'target_type',
    ];

    protected function casts(): array
    {
        return [
            'snapshot' => 'encrypted:array',
            'executed_at' => 'datetime',
        ];
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by_id');
    }

    public function executor()
    {
        return $this->belongsTo(User::class, 'executed_by_id');
    }

    public function approvalRequest()
    {
        return $this->morphOne(ApprovalRequest::class, 'subject');
    }

    public function markAsPendingCeo(): void
    {
        $this->forceFill(['status' => 'pending_ceo'])->save();
    }

    public function markAsRejected(): void
    {
        $this->forceFill(['status' => 'rejected'])->save();
    }
}
