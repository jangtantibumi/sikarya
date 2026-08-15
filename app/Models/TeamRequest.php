<?php

namespace App\Models;

use App\Services\EmployeeIdentityService;
use App\Services\EmployeeSeparationService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TeamRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'requester_username',
        'action',
        'target_username',
        'details',
        'status',
    ];

    protected $casts = [
        'details' => 'array', // automatically decode JSON to array
    ];

    public function approvalRequest()
    {
        return $this->morphOne(ApprovalRequest::class, 'subject');
    }

    public function markAsApproved(?User $approver = null): void
    {
        DB::transaction(function () use ($approver): void {
            $this->refresh();

            if ($this->status === 'approved') {
                return;
            }

            if ($this->action === 'add') {
                $details = $this->details ?? [];

                if (empty($details['email']) || empty($details['name']) || empty($details['role'])) {
                    throw ValidationException::withMessages([
                        'details' => 'Data staf baru belum lengkap.',
                    ]);
                }

                if (User::query()->where('email', $details['email'])->exists()) {
                    throw ValidationException::withMessages([
                        'details.email' => "Email {$details['email']} sudah terdaftar.",
                    ]);
                }

                app(EmployeeIdentityService::class)->createUser([
                    ...$details,
                    'parent' => $this->requester_username,
                ]);
            }

            if ($this->action === 'delete') {
                $target = User::query()->where('username', $this->target_username)->first();

                if ($target && ! $target->isCEO()) {
                    $initiator = User::query()
                        ->where('username', $this->requester_username)
                        ->firstOrFail();
                    $separationDetails = array_replace([
                        'completion_status' => 'incomplete',
                        'convert_to_alumni' => false,
                        'separation_reason' => 'other',
                        'separation_notes' => 'Pengajuan lama diproses setelah pembaruan pencatatan status keluar.',
                        'effective_date' => now()->toDateString(),
                    ], $this->details ?? []);
                    app(EmployeeSeparationService::class)->separate(
                        $target,
                        $initiator,
                        $approver ?: $initiator,
                        $separationDetails,
                        $this,
                    );
                }
            }

            $this->forceFill(['status' => 'approved'])->save();
        });
    }

    public function markAsRejected(): void
    {
        $this->forceFill(['status' => 'rejected'])->save();
    }
}
