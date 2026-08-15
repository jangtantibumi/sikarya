<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureErpAccessGate;
use App\Models\AuditEvent;
use App\Services\DataRetentionService;
use App\Services\FeatureManager;
use App\Services\SecurityAuditService;
use App\Services\SecuritySettingsService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SystemControlController extends Controller
{
    public function __construct(
        private readonly FeatureManager $features,
        private readonly SecuritySettingsService $settings,
        private readonly SecurityAuditService $audit,
        private readonly DataRetentionService $retention,
    ) {}

    public function show(Request $request)
    {
        $this->authorizeCEO($request);

        return response()->json([
            'features' => $this->features->catalogue(),
            'security' => $this->settings->publicConfiguration(),
            'retention' => $this->retention->configuration(),
            'server_time' => now()->toIso8601String(),
        ]);
    }

    public function updateFeature(Request $request, string $feature)
    {
        $actor = $this->authorizeCEO($request);
        $validated = $request->validate([
            'enabled' => ['required', 'boolean'],
        ]);
        $updated = $this->features->set($feature, (bool) $validated['enabled'], $actor);

        $this->audit->record(
            'feature.updated',
            actor: $actor,
            request: $request,
            metadata: ['feature' => $feature, 'enabled' => (bool) $validated['enabled']],
            subjectType: 'feature',
            subjectId: $feature,
        );

        return response()->json([
            'success' => true,
            'message' => ($updated['enabled'] ?? false)
                ? "Modul {$updated['label']} berhasil diaktifkan."
                : "Modul {$updated['label']} berhasil dinonaktifkan.",
            'feature' => $updated,
            'feature_states' => $this->features->states(),
        ]);
    }

    public function updateRetention(Request $request)
    {
        $actor = $this->authorizeCEO($request);
        $validated = $request->validate([
            'archive_inactive_days' => ['required', 'integer', 'between:1,3650'],
            'anonymize_inactive_days' => ['required', 'integer', 'between:365,7300'],
            'auto_anonymize' => ['required', 'boolean'],
            'purge_soft_deleted_days' => ['required', 'integer', 'between:90,3650'],
            'auto_purge' => ['required', 'boolean'],
            'storage_warning_mb' => ['required', 'integer', 'between:100,102400'],
        ]);

        if ($validated['anonymize_inactive_days'] <= $validated['archive_inactive_days']) {
            throw ValidationException::withMessages([
                'anonymize_inactive_days' => 'Masa anonimisasi harus lebih panjang daripada masa arsip.',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Kebijakan retensi data berhasil diperbarui.',
            'retention' => $this->retention->update($validated, $actor),
        ]);
    }

    public function runRetention(Request $request)
    {
        $actor = $this->authorizeCEO($request);
        $metrics = $this->retention->run($actor, 'manual');

        return response()->json([
            'success' => true,
            'message' => 'Siklus arsip dan retensi selesai dijalankan.',
            'metrics' => $metrics,
            'retention' => $this->retention->configuration(),
        ]);
    }

    public function updateSecurity(Request $request)
    {
        $actor = $this->authorizeCEO($request);
        $validated = $request->validate([
            'otp_expires_minutes' => ['required', 'integer', 'between:2,10'],
            'otp_resend_seconds' => ['required', 'integer', 'between:30,300'],
            'otp_max_attempts' => ['required', 'integer', 'between:3,10'],
            'otp_lock_minutes' => ['required', 'integer', 'between:5,60'],
            'gate_enabled' => ['required', 'boolean'],
            'gate_session_hours' => ['required', 'integer', 'between:1,168'],
        ]);

        if ($validated['gate_enabled'] && ! $this->settings->gateConfigured()) {
            throw ValidationException::withMessages([
                'gate_enabled' => 'Tetapkan password akses perusahaan terlebih dahulu.',
            ]);
        }

        $map = [
            'security.otp.expires_minutes' => $validated['otp_expires_minutes'],
            'security.otp.resend_seconds' => $validated['otp_resend_seconds'],
            'security.otp.max_attempts' => $validated['otp_max_attempts'],
            'security.otp.lock_minutes' => $validated['otp_lock_minutes'],
            'security.gate.enabled' => (bool) $validated['gate_enabled'],
            'security.gate.session_hours' => $validated['gate_session_hours'],
        ];

        foreach ($map as $key => $value) {
            $this->settings->set($key, $value, $actor);
        }

        if ($validated['gate_enabled']) {
            EnsureErpAccessGate::grant($request);
        }

        $this->audit->record(
            'security.settings_updated',
            actor: $actor,
            request: $request,
            metadata: collect($validated)->except([])->all(),
            subjectType: 'system_security',
            subjectId: 'global',
        );

        return response()->json([
            'success' => true,
            'message' => 'Kebijakan keamanan berhasil diperbarui.',
            'security' => $this->settings->publicConfiguration(),
        ]);
    }

    public function updateGatePassword(Request $request)
    {
        $actor = $this->authorizeCEO($request);
        $validated = $request->validate([
            'password' => [
                'required',
                'string',
                'min:12',
                'max:255',
                'confirmed',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[^A-Za-z0-9]/',
            ],
            'enable_gate' => ['nullable', 'boolean'],
        ], [
            'password.regex' => 'Password harus memiliki huruf kecil, huruf besar, angka, dan simbol.',
        ]);

        $this->settings->setGatePassword($validated['password'], $actor);
        if ((bool) ($validated['enable_gate'] ?? true)) {
            $this->settings->set('security.gate.enabled', true, $actor);
            EnsureErpAccessGate::grant($request);
        }

        $this->audit->record(
            'access_gate.password_rotated',
            actor: $actor,
            request: $request,
            metadata: ['gate_enabled' => (bool) ($validated['enable_gate'] ?? true)],
            subjectType: 'system_security',
            subjectId: 'access_gate',
        );

        return response()->json([
            'success' => true,
            'message' => 'Password akses perusahaan berhasil disimpan dan tidak akan ditampilkan kembali.',
            'security' => $this->settings->publicConfiguration(),
        ]);
    }

    public function updateMail(Request $request)
    {
        $actor = $this->authorizeCEO($request);
        $validated = $request->validate([
            'host' => ['required', 'string', 'max:255'],
            'port' => ['required', 'integer', 'between:1,65535'],
            'scheme' => ['required', 'string', 'in:smtp,smtps'],
            'username' => ['required', 'email', 'max:255'],
            'password' => ['nullable', 'string', 'min:16', 'max:255'],
            'from_address' => ['required', 'email', 'max:255'],
            'from_name' => ['required', 'string', 'max:120'],
        ]);

        if (blank($validated['password'] ?? null) && ! $this->settings->mailConfigured()) {
            throw ValidationException::withMessages([
                'password' => 'App Password SMTP wajib diisi saat konfigurasi pertama.',
            ]);
        }

        $map = [
            'mail.smtp.host' => $validated['host'],
            'mail.smtp.port' => $validated['port'],
            'mail.smtp.scheme' => $validated['scheme'],
            'mail.smtp.username' => $validated['username'],
            'mail.from.address' => $validated['from_address'],
            'mail.from.name' => $validated['from_name'],
        ];

        foreach ($map as $key => $value) {
            $this->settings->set($key, $value, $actor);
        }

        if (filled($validated['password'] ?? null)) {
            $this->settings->set('mail.smtp.password', $validated['password'], $actor, true);
        }

        $this->settings->applyMailConfiguration();
        $this->audit->record(
            'mail.smtp_updated',
            actor: $actor,
            request: $request,
            metadata: [
                'host' => $validated['host'],
                'port' => $validated['port'],
                'scheme' => $validated['scheme'],
                'username' => $validated['username'],
                'from_address' => $validated['from_address'],
            ],
            subjectType: 'system_security',
            subjectId: 'smtp',
        );

        return response()->json([
            'success' => true,
            'message' => 'SMTP berhasil disimpan terenkripsi. Minta OTP baru untuk menguji pengiriman.',
            'security' => $this->settings->publicConfiguration(),
        ]);
    }

    public function auditEvents(Request $request)
    {
        $this->authorizeCEO($request);

        return response()->json(
            AuditEvent::query()
                ->with('actor:id,name,username')
                ->latest('id')
                ->limit(100)
                ->get()
                ->map(fn (AuditEvent $event): array => [
                    'id' => $event->id,
                    'event_type' => $event->event_type,
                    'actor' => $event->actor?->only(['name', 'username']),
                    'subject_type' => $event->subject_type,
                    'subject_id' => $event->subject_id,
                    'ip_address' => $event->ip_address,
                    'metadata' => $event->metadata,
                    'integrity' => substr($event->event_hash, 0, 12),
                    'created_at' => $event->created_at?->toIso8601String(),
                ]),
        );
    }

    private function authorizeCEO(Request $request)
    {
        $actor = $request->user();
        abort_unless($actor?->isCEO(), 403);

        return $actor;
    }
}
