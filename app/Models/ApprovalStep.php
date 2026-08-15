<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalStep extends Model
{
    protected $fillable = [
        'approval_request_id', 'sequence', 'approver_id', 'approver_role',
        'status', 'decision_note', 'decided_at',
    ];

    protected $casts = [
        'decided_at' => 'datetime',
    ];

    public function request()
    {
        return $this->belongsTo(ApprovalRequest::class, 'approval_request_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
