<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AlumniProfile;
use App\Models\EmployeeSeparation;
use App\Models\TeamRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EmployeeSeparationService
{
    public function __construct(
        private readonly SecurityAuditService $audit,
        private readonly WorkflowNotificationService $notifications,
        private readonly EmployeeDataExportService $exports,
    ) {}

    public function separate(
        User $target,
        User $initiator,
        User $approver,
        array $details,
        ?TeamRequest $teamRequest = null,
        ?Request $request = null,
    ): EmployeeSeparation {
        $record = DB::transaction(function () use (
            $target,
            $initiator,
            $approver,
            $details,
            $teamRequest,
        ): EmployeeSeparation {
            $convertToAlumni = (bool) ($details['convert_to_alumni'] ?? false);
            if ($convertToAlumni && ($details['completion_status'] ?? null) !== 'completed') {
                throw ValidationException::withMessages([
                    'convert_to_alumni' => 'Hanya anggota yang menyelesaikan masa kerja atau magang yang dapat dijadikan alumni.',
                ]);
            }

            $newParent = $target->parent ?: 'ceo';
            User::query()
                ->where('parent', $target->username)
                ->where('is_active', true)
                ->update(['parent' => $newParent]);

            $formerRole = $target->role;
            $backupPath = $this->exports->archive($target);
            $formerDivision = $target->divisionKey();
            $formerParent = $target->parent;

            $target->forceFill([
                'is_active' => $convertToAlumni,
                'account_status' => $convertToAlumni ? 'alumni' : 'inactive',
                'former_role' => $formerRole,
                'former_parent' => $formerParent,
                'role' => $convertToAlumni ? 'alumni' : $formerRole,
                'parent' => null,
                'deactivated_at' => now(),
                'alumni_since' => $convertToAlumni ? now() : null,
                'archived_at' => null,
                'otp_code' => null,
                'otp_expires_at' => null,
            ])->save();

            if ($convertToAlumni) {
                AlumniProfile::query()->updateOrCreate(
                    ['user_id' => $target->id],
                    [
                        'former_role' => $formerRole,
                        'former_division' => $formerDivision,
                        'receive_event_invitations' => true,
                    ],
                );
            }

            return EmployeeSeparation::query()->updateOrCreate(
                ['user_id' => $target->id],
                [
                    'initiated_by_id' => $initiator->id,
                    'approved_by_id' => $approver->id,
                    'team_request_id' => $teamRequest?->id,
                    'completion_status' => $details['completion_status'],
                    'converted_to_alumni' => $convertToAlumni,
                    'backup_path' => $backupPath,
                    'separation_reason' => $details['separation_reason'],
                    'notes' => $details['separation_notes'] ?? null,
                    'effective_date' => $details['effective_date'] ?? now()->toDateString(),
                    'status' => 'approved',
                ],
            );
        });

        $this->audit->record(
            'employee.separated',
            actor: $approver,
            request: $request,
            metadata: [
                'username' => $target->username,
                'completion_status' => $record->completion_status,
                'separation_reason' => $record->separation_reason,
                'effective_date' => $record->effective_date?->format('Y-m-d'),
                'converted_to_alumni' => $record->converted_to_alumni,
                'team_request_id' => $teamRequest?->id,
            ],
            subjectType: User::class,
            subjectId: $target->id,
        );

        $reasonLabels = [
            'completed' => 'masa kerja/kontrak selesai',
            'terminated' => 'hubungan kerja dihentikan perusahaan',
            'resigned' => 'mengundurkan diri',
            'other' => 'alasan lain yang terdokumentasi',
        ];
        $this->notifications->send(
            $this->notifications->hrdUsers(),
            'Pembaruan status anggota tim',
            $record->converted_to_alumni
                ? "{$target->name} telah menyelesaikan masa kerja dan dialihkan ke portal alumni."
                : "{$target->name} dinonaktifkan karena ".($reasonLabels[$record->separation_reason] ?? 'alasan yang terdokumentasi').'.',
            "employee:{$target->id}:separated:hrd",
            'organization',
            '/#hierarchy',
            [
                'user_id' => $target->id,
                'separation_id' => $record->id,
                'converted_to_alumni' => $record->converted_to_alumni,
            ],
        );

        if ($record->converted_to_alumni) {
            $this->notifications->send(
                $target->fresh(),
                'Akun alumni Suba-Arch aktif',
                'Masa kerja Anda telah selesai. Gunakan email yang sama dan OTP untuk masuk ke portal alumni serta memperbarui portofolio.',
                "employee:{$target->id}:alumni:welcome",
                'alumni',
                '/#alumni',
                ['user_id' => $target->id],
            );
        }

        return $record;
    }
}
