<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ResignationRequest extends Model
{
    use BelongsToCompany, SoftDeletes;

    protected $fillable = [
        'user_id',
        'last_working_date',
        'reason',
        'handover_notes',
        'status',
    ];

    protected $casts = [
        'last_working_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approvalRequest()
    {
        return $this->morphOne(ApprovalRequest::class, 'subject');
    }

    public function markAsPendingCeo(): void
    {
        $this->forceFill(['status' => 'pending_ceo'])->save();
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
