<?php

namespace App\Http\Controllers;

use App\Mail\LoginOtpMail;
use App\Models\User;
use App\Services\SecurityAuditService;
use App\Services\SecuritySettingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Throwable;

class AuthController extends Controller
{
    public function __construct(
        private readonly SecuritySettingsService $settings,
        private readonly SecurityAuditService $audit,
    ) {
    }

    public function sendOtp(Request $request)
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255'],
        ]);
        $identifier = trim($validated['username']);
        $identifierHash = hash('sha256', mb_strtolower($identifier));
        $genericMessage = 'Jika akun aktif dan email terdaftar, kode OTP akan dikirim. Periksa kotak masuk dan folder spam.';
        $user = $this->findActiveUser($identifier);

        if (!$user) {
            $this->audit->record(
                'otp.request_unknown',
                request: $request,
                metadata: ['identifier_hash' => $identifierHash],
            );

            return response()->json([
                'success' => true,
                'message' => $genericMessage,
            ]);
        }

        $policy = $this->settings->otp();
        $now = now();

        if ($user->otp_locked_until?->isFuture()) {
            $this->audit->record(
                'otp.request_blocked',
                actor: $user,
                request: $request,
                metadata: ['reason' => 'account_locked'],
            );

            return response()->json([
                'success' => true,
                'message' => $genericMessage,
            ]);
        }

        if (
            $user->otp_last_sent_at
            && $user->otp_last_sent_at->copy()->addSeconds($policy['resend_seconds'])->isFuture()
        ) {
            $this->audit->record(
                'otp.request_throttled',
                actor: $user,
                request: $request,
                metadata: ['resend_seconds' => $policy['resend_seconds']],
            );

            return response()->json([
                'success' => true,
                'message' => $genericMessage,
            ]);
        }

        $otp = (string) random_int(100000, 999999);
        $user->forceFill([
            'otp_code' => Hash::make($otp),
            'otp_expires_at' => $now->copy()->addMinutes($policy['expires_minutes']),
            'otp_attempts' => 0,
            'otp_locked_until' => null,
            'otp_last_sent_at' => $now,
        ])->save();

        $mailSent = false;
        try {
            $this->settings->applyMailConfiguration();
            Mail::to($user->email)->send(
                new LoginOtpMail($otp, $policy['expires_minutes']),
            );
            $mailSent = true;
        } catch (Throwable $exception) {
            report($exception);
        }

        $this->audit->record(
            $mailSent ? 'otp.sent' : 'otp.delivery_failed',
            actor: $user,
            request: $request,
            metadata: [
                'channel' => 'email',
                'expires_minutes' => $policy['expires_minutes'],
                'mail_driver' => config('mail.default'),
            ],
        );

        return response()->json([
            'success' => true,
            'message' => $genericMessage,
            'otp_digits' => 6,
            'expires_minutes' => $policy['expires_minutes'],
            'resend_seconds' => $policy['resend_seconds'],
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'otp' => ['required', 'digits:6'],
        ]);
        $identifier = trim($validated['username']);
        $policy = $this->settings->otp();

        return DB::transaction(function () use ($request, $identifier, $validated, $policy) {
            $user = User::query()
                ->where('is_active', true)
                ->where(function ($query) use ($identifier): void {
                    $query->where('username', $identifier)
                        ->orWhere('email', $identifier);
                })
                ->lockForUpdate()
                ->first();

            if (!$user) {
                $this->audit->record(
                    'otp.verify_failed',
                    request: $request,
                    metadata: [
                        'reason' => 'unknown_account',
                        'identifier_hash' => hash('sha256', mb_strtolower($identifier)),
                    ],
                );

                return $this->otpError('Kode OTP tidak valid atau sudah kedaluwarsa.');
            }

            if ($user->otp_locked_until?->isFuture()) {
                $this->audit->record(
                    'otp.verify_blocked',
                    actor: $user,
                    request: $request,
                    metadata: ['reason' => 'account_locked'],
                );

                return $this->otpError('Terlalu banyak percobaan. Akun dikunci sementara. Silakan coba kembali nanti.');
            }

            if (!$user->otp_code || !$user->otp_expires_at || $user->otp_expires_at->isPast()) {
                $user->forceFill([
                    'otp_code' => null,
                    'otp_expires_at' => null,
                    'otp_attempts' => 0,
                ])->save();

                $this->audit->record(
                    'otp.verify_failed',
                    actor: $user,
                    request: $request,
                    metadata: ['reason' => 'expired_or_missing'],
                );

                return $this->otpError('Kode OTP tidak valid atau sudah kedaluwarsa.');
            }

            try {
                $valid = Hash::check((string) $validated['otp'], (string) $user->otp_code);
            } catch (Throwable) {
                $valid = false;
            }

            if (!$valid) {
                $attempts = ((int) $user->otp_attempts) + 1;
                $locked = $attempts >= $policy['max_attempts'];
                $user->forceFill([
                    'otp_attempts' => $attempts,
                    'otp_locked_until' => $locked ? now()->addMinutes($policy['lock_minutes']) : null,
                    'otp_code' => $locked ? null : $user->otp_code,
                    'otp_expires_at' => $locked ? null : $user->otp_expires_at,
                ])->save();

                $this->audit->record(
                    $locked ? 'otp.account_locked' : 'otp.verify_failed',
                    actor: $user,
                    request: $request,
                    metadata: [
                        'reason' => 'mismatch',
                        'attempts' => $attempts,
                        'max_attempts' => $policy['max_attempts'],
                    ],
                );

                return $this->otpError(
                    $locked
                        ? 'Terlalu banyak percobaan. Akun dikunci sementara.'
                        : 'Kode OTP tidak valid atau sudah kedaluwarsa.',
                );
            }

            $user->forceFill([
                'otp_code' => null,
                'otp_expires_at' => null,
                'otp_attempts' => 0,
                'otp_locked_until' => null,
                'email_verified_at' => $user->email_verified_at ?? now(),
            ])->save();

            Auth::login($user);
            $request->session()->regenerate();
            $sessionStartedAt = now();
            $request->session()->put('absolute_login_at', $sessionStartedAt->timestamp);

            $this->audit->record(
                'auth.login_succeeded',
                actor: $user,
                request: $request,
                metadata: ['method' => 'email_otp'],
            );

            return response()->json([
                'success' => true,
                'message' => 'Login OTP berhasil.',
                'user' => $this->formatUser($user),
                'server_time' => $sessionStartedAt->toIso8601String(),
                'session_expires_at' => $sessionStartedAt->copy()->addWeek()->toIso8601String(),
            ]);
        });
    }

    public function logout(Request $request)
    {
        $actor = $request->user();
        $this->audit->record(
            'auth.logout',
            actor: $actor,
            request: $request,
        );

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'success' => true,
            'message' => 'Sesi berhasil diakhiri.',
        ]);
    }

    private function findActiveUser(string $identifier): ?User
    {
        return User::query()
            ->where('is_active', true)
            ->where(function ($query) use ($identifier): void {
                $query->where('username', $identifier)
                    ->orWhere('email', $identifier);
            })
            ->first();
    }

    private function otpError(string $message)
    {
        return response()->json([
            'message' => $message,
            'errors' => [
                'otp' => [$message],
            ],
        ], 422);
    }

    private function formatUser(User $user): array
    {
        $roleTitles = [
            'ceo' => 'Chief Executive Officer',
            'mgr_marketing' => 'Marketing Manager',
            'staff_marketing' => 'Marketing Staff',
            'mgr_ops' => 'Operations Manager',
            'staff_ops' => 'Operations Staff',
            'mgr_finance' => 'Finance Manager',
            'staff_finance' => 'Finance Staff',
            'mgr_hrd' => 'HRD Manager',
            'staff_hrd' => 'HRD Staff',
            'alumni' => 'Suba-Arch Alumni',
        ];

        return [
            'name' => $user->name,
            'username' => $user->username,
            'role' => $user->role,
            'email' => $user->email,
            'parent' => $user->parent ?: 'ceo',
            'level' => $user->isAlumni()
                ? 'Alumni Network'
                : ($user->isCEO()
                    ? 'Level 1 - CEO'
                    : ($user->isManager() ? 'Level 2 - Manager' : 'Level 3 - Staff')),
            'title' => $user->job_title ?: ($roleTitles[$user->role] ?? 'Karyawan'),
            'avatar' => strtoupper(substr($user->username, 0, 2)),
            'employment_type' => $user->employment_type,
            'account_status' => $user->account_status,
            'alumni_since' => $user->alumni_since?->toIso8601String(),
        ];
    }
}
