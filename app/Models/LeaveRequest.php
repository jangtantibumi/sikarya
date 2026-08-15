<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveRequest extends Model
{
    use BelongsToCompany, SoftDeletes;

    protected $fillable = [
        'company_id', 'user_id', 'start_date', 'end_date', 'reason', 'type', 'leave_type', 'total_days', 'status', 'approved_by'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function markAsApproved(): void
    {
        $this->forceFill(['status' => 'approved'])->save();
    }

    public function markAsRejected(): void
    {
        $this->forceFill(['status' => 'rejected'])->save();
    }

    public function markAsPendingCeo(): void
    {
        $this->forceFill(['status' => 'pending_ceo'])->save();
    }

    public function markAsCancellationRequested(): void
    {
        $this->forceFill(['status' => 'cancellation_requested'])->save();
    }
}
