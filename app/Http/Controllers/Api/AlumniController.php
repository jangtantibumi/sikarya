<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\AlumniEventInvitationMail;
use App\Models\AlumniEventInvitation;
use App\Models\AlumniInvitationRecipient;
use App\Models\AlumniProfile;
use App\Models\ChatMessage;
use App\Models\User;
use App\Services\SecurityAuditService;
use App\Services\SecuritySettingsService;
use App\Services\WorkflowNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Throwable;

class AlumniController extends Controller
{
    public function __construct(
        private readonly SecuritySettingsService $settings,
        private readonly SecurityAuditService $audit,
        private readonly WorkflowNotificationService $notifications,
    ) {}

    public function index(Request $request)
    {
        $actor = $request->user();
        $directory = User::query()
            ->where('is_active', true)
            ->where('account_status', 'alumni')
            ->with('alumniProfile')
            ->orderBy('name')
            ->get()
            ->map(fn (User $user): array => $this->formatProfile($user, true));

        $manageable = collect();
        if ($actor->isCEO() || $actor->isHRD()) {
            $manageable = $directory;
        } elseif ($actor->isManager()) {
            $manageable = User::query()
                ->where('is_active', true)
                ->where('account_status', 'alumni')
                ->where('former_parent', $actor->username)
                ->with('alumniProfile')
                ->orderBy('name')
                ->get()
                ->map(fn (User $user): array => $this->formatProfile($user));
        }

        $invitations = ($actor->isCEO() || $actor->isHRD() || $actor->isManager()) ? AlumniEventInvitation::query()
            ->with('creator:id,name')
            ->when(
                $actor->isManager() && ! $actor->isHRD(),
                fn ($query) => $query->where('division', $actor->divisionKey()),
            )
            ->latest('id')
            ->limit(30)->get() : collect();

        $announcements = ChatMessage::query()
            ->with('sender:id,name,role')
            ->whereIn('type', ['holiday_announcement', 'alumni_announcement'])
            ->latest('id')->limit(20)->get()
            ->filter(fn (ChatMessage $message) => $this->canSeeAnnouncement($actor, $message))
            ->map(fn (ChatMessage $message) => [
                'id' => $message->id,
                'title' => data_get($message->metadata, 'title', 'Pengumuman Suba-Arch'),
                'message' => $message->message,
                'date' => $message->created_at?->toIso8601String(),
                'author' => $message->sender?->name,
                'division' => data_get($message->metadata, 'division'),
            ]);

        return response()->json([
            'alumni' => $directory,
            'manageable_alumni' => $manageable,
            'invitations' => $invitations,
            'announcements' => $announcements,
        ]);
    }

    public function profile(Request $request)
    {
        $user = $request->user();
        abort_unless($user->isAlumni(), 403);

        $user->load('alumniProfile');

        return response()->json($this->formatProfile($user));
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        abort_unless($user->isAlumni(), 403);

        $validated = $request->validate([
            'current_employer' => ['nullable', 'string', 'max:255'],
            'current_position' => ['nullable', 'string', 'max:255'],
            'industry' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'linkedin_url' => ['nullable', 'url:http,https', 'max:255'],
            'portfolio_url' => ['nullable', 'url:http,https', 'max:255'],
            'bio' => ['nullable', 'string', 'max:3000'],
            'skills' => ['nullable', 'array', 'max:30'],
            'skills.*' => ['string', 'max:80'],
            'available_for_opportunities' => ['required', 'boolean'],
            'receive_event_invitations' => ['required', 'boolean'],
        ]);

        $profile = AlumniProfile::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                ...$validated,
                'former_role' => $user->former_role,
                'former_division' => $user->alumniProfile?->former_division,
                'last_profile_update_at' => now(),
            ],
        );

        $this->audit->record(
            'alumni.profile_updated',
            actor: $user,
            request: $request,
            subjectType: AlumniProfile::class,
            subjectId: $profile->id,
        );

        return response()->json([
            'success' => true,
            'message' => 'Profil alumni berhasil diperbarui.',
            'profile' => $this->formatProfile($user->fresh('alumniProfile')),
        ]);
    }

    public function invite(Request $request)
    {
        $actor = $request->user();
        abort_unless($actor->isCEO() || $actor->isHRD() || $actor->isManager(), 403);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
            'event_at' => ['required', 'date', 'after:now'],
            'location' => ['nullable', 'string', 'max:255'],
            'registration_url' => ['nullable', 'url:http,https', 'max:255'],
            'recipient_ids' => ['nullable', 'array', 'max:500'],
            'recipient_ids.*' => [
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query
                    ->where('account_status', 'alumni')
                    ->where('is_active', true)),
            ],
        ]);

        $isDivisionManager = $actor->isManager() && ! $actor->isHRD() && ! $actor->isCEO();
        $division = $isDivisionManager ? $actor->divisionKey() : null;
        abort_if($isDivisionManager && ! $division, 422, 'Divisi manager tidak dapat dikenali.');

        $recipients = User::query()
            ->where('is_active', true)
            ->where('account_status', 'alumni')
            ->whereHas('alumniProfile', fn ($profiles) => $profiles->where('receive_event_invitations', true))
            ->when($division, fn ($users) => $users->whereHas('alumniProfile', fn ($profiles) => $profiles->where('former_division', $division)))
            ->when(
                ! empty($validated['recipient_ids']),
                fn ($users) => $users->whereIn('id', $validated['recipient_ids']),
            )
            ->orderBy('id')
            ->limit(500)
            ->get();

        if ($recipients->isEmpty()) {
            return response()->json([
                'message' => 'Belum ada alumni aktif yang mengizinkan undangan event.',
                'errors' => ['recipient_ids' => ['Tidak ada penerima yang memenuhi syarat.']],
            ], 422);
        }

        $invitation = DB::transaction(function () use ($validated, $actor, $recipients, $division): AlumniEventInvitation {
            $invitation = AlumniEventInvitation::query()->create([
                ...collect($validated)->except('recipient_ids')->all(),
                'created_by_id' => $actor->id,
                'division' => $division,
            ]);

            foreach ($recipients as $recipient) {
                $invitation->recipients()->create([
                    'user_id' => $recipient->id,
                    'email' => $recipient->email,
                    'status' => 'pending',
                ]);
            }

            return $invitation;
        });

        $sent = 0;
        $failed = 0;
        $this->settings->applyMailConfiguration();

        foreach ($recipients as $recipient) {
            /** @var AlumniInvitationRecipient $delivery */
            $delivery = $invitation->recipients()->where('user_id', $recipient->id)->firstOrFail();
            try {
                Mail::to($recipient->email)->send(new AlumniEventInvitationMail($invitation, $recipient));
                $delivery->forceFill(['status' => 'sent', 'sent_at' => now()])->save();
                $sent++;

                $this->notifications->send(
                    $recipient,
                    "Undangan alumni: {$invitation->title}",
                    $invitation->message,
                    "alumni:event:{$invitation->id}:{$recipient->id}",
                    'alumni',
                    '/#alumni',
                    ['invitation_id' => $invitation->id],
                );
            } catch (Throwable $exception) {
                report($exception);
                $delivery->forceFill([
                    'status' => 'failed',
                    'error_message' => mb_substr($exception->getMessage(), 0, 1000),
                ])->save();
                $failed++;
            }
        }

        $invitation->forceFill(['sent_count' => $sent, 'failed_count' => $failed])->save();
        $this->audit->record(
            'alumni.invitation_sent',
            actor: $actor,
            request: $request,
            metadata: ['invitation_id' => $invitation->id, 'sent' => $sent, 'failed' => $failed],
            subjectType: AlumniEventInvitation::class,
            subjectId: $invitation->id,
        );

        return response()->json([
            'success' => true,
            'message' => "Undangan selesai diproses: {$sent} terkirim, {$failed} gagal.",
            'invitation' => $invitation,
        ], 201);
    }

    public function announce(Request $request)
    {
        $actor = $request->user();
        abort_unless($actor->isManager() || $actor->isCEO() || $actor->isHRD(), 403);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $isDivisionManager = $actor->isManager() && ! $actor->isHRD() && ! $actor->isCEO();
        $division = $isDivisionManager ? $actor->divisionKey() : null;
        abort_if($isDivisionManager && ! $division, 422, 'Divisi manager tidak dapat dikenali.');

        $announcement = ChatMessage::query()->create([
            'sender_id' => $actor->id,
            'channel' => 'general',
            'type' => 'alumni_announcement',
            'message' => $validated['message'],
            'metadata' => [
                'title' => $validated['title'],
                'division' => $division,
                'audience' => $division ? 'division_alumni' : 'all_alumni',
            ],
        ]);

        $recipients = User::query()
            ->where('is_active', true)
            ->where('account_status', 'alumni')
            ->when($division, fn ($users) => $users->whereHas('alumniProfile', fn ($profiles) => $profiles->where('former_division', $division)))
            ->get();

        $divisionLabel = $division ? $this->divisionLabel($division) : 'Suba-Arch';
        $this->notifications->send(
            $recipients,
            "Alumni Network · {$divisionLabel}",
            $validated['title'],
            "alumni:announcement:{$announcement->id}",
            'alumni',
            '/#alumni',
            ['chat_message_id' => $announcement->id, 'division' => $division],
        );

        $this->audit->record(
            'alumni.announcement_published',
            actor: $actor,
            request: $request,
            metadata: ['chat_message_id' => $announcement->id, 'division' => $division, 'recipients' => $recipients->count()],
            subjectType: ChatMessage::class,
            subjectId: $announcement->id,
        );

        return response()->json([
            'success' => true,
            'message' => "Pengumuman diterbitkan untuk {$recipients->count()} alumni {$divisionLabel}.",
        ], 201);
    }

    private function canSeeAnnouncement(User $actor, ChatMessage $message): bool
    {
        if ($message->type === 'holiday_announcement' || $actor->isCEO() || $actor->isHRD()) {
            return true;
        }

        $division = data_get($message->metadata, 'division');
        if (! $division) {
            return true;
        }

        if ($actor->isAlumni()) {
            return $actor->alumniProfile?->former_division === $division;
        }

        return $actor->divisionKey() === $division;
    }

    private function divisionLabel(string $division): string
    {
        return match ($division) {
            'marketing' => 'Marketing',
            'operasional' => 'Operasional',
            'finance' => 'Finance',
            'hrd' => 'HRD',
            default => ucfirst($division),
        };
    }

    private function formatProfile(User $user, bool $directoryOnly = false): array
    {
        $profile = $user->alumniProfile;

        return [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $directoryOnly ? null : $user->email,
            'employee_code' => $user->employee_code,
            'alumni_since' => $user->alumni_since?->toIso8601String(),
            'former_role' => $profile?->former_role ?? $user->former_role,
            'former_division' => $profile?->former_division,
            'current_employer' => $profile?->current_employer,
            'current_position' => $profile?->current_position,
            'industry' => $profile?->industry,
            'city' => $profile?->city,
            'linkedin_url' => $profile?->linkedin_url,
            'portfolio_url' => $profile?->portfolio_url,
            'bio' => $profile?->bio,
            'skills' => $profile?->skills ?? [],
            'available_for_opportunities' => (bool) $profile?->available_for_opportunities,
            'receive_event_invitations' => (bool) ($profile?->receive_event_invitations ?? true),
            'last_profile_update_at' => $profile?->last_profile_update_at?->toIso8601String(),
        ];
    }
}
