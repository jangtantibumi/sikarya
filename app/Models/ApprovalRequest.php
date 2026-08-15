<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class ApprovalRequest extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'request_type', 'division', 'requester_id', 'subject_type', 'subject_id',
        'current_approver_id', 'current_step', 'status', 'payload', 'submitted_at', 'completed_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'submitted_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function subject()
    {
        return $this->morphTo();
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function currentApprover()
    {
        return $this->belongsTo(User::class, 'current_approver_id');
    }

    public function steps()
    {
        return $this->hasMany(ApprovalStep::class);
    }
}
