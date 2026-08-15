<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class SecuritySettingsService
{
    private const CACHE_PREFIX = 'erp.system-setting.';

    private const DEFAULTS = [
        'security.otp.expires_minutes' => 5,
        'security.otp.resend_seconds' => 60,
        'security.otp.max_attempts' => 5,
        'security.otp.lock_minutes' => 15,
        'security.gate.enabled' => false,
        'security.gate.session_hours' => 12,
    ];

    public function get(string $key, mixed $default = null): mixed
    {
        $fallback = self::DEFAULTS[$key] ?? $default;
        if (! Schema::hasTable('system_settings')) {
            return $fallback;
        }

        $stored = Cache::remember(
            self::CACHE_PREFIX.$key,
            now()->addMinutes(10),
            fn () => SystemSetting::query()->where('key', $key)->first()?->value,
        );

        if ($stored === null) {
            return $fallback;
        }

        return match (true) {
            is_bool($fallback) => filter_var($stored, FILTER_VALIDATE_BOOL),
            is_int($fallback) => (int) $stored,
            is_float($fallback) => (float) $stored,
            default => $stored,
        };
    }

    public function set(string $key, mixed $value, User $actor, bool $secret = false): void
    {
        SystemSetting::query()->updateOrCreate(
            ['key' => $key],
            [
                'value' => is_bool($value) ? ($value ? '1' : '0') : (string) $value,
                'is_secret' => $secret,
                'updated_by' => $actor->id,
            ],
        );

        Cache::forget(self::CACHE_PREFIX.$key);
    }

    public function otp(): array
    {
        return [
            'expires_minutes' => $this->get('security.otp.expires_minutes'),
            'resend_seconds' => $this->get('security.otp.resend_seconds'),
            'max_attempts' => $this->get('security.otp.max_attempts'),
            'lock_minutes' => $this->get('security.otp.lock_minutes'),
        ];
    }

    public function gateEnabled(): bool
    {
        return (bool) $this->get('security.gate.enabled', false) && $this->gateConfigured();
    }

    public function gateConfigured(): bool
    {
        return is_string($this->get('security.gate.password_hash')) && $this->get('security.gate.password_hash') !== '';
    }

    public function verifyGatePassword(string $password): bool
    {
        $hash = $this->get('security.gate.password_hash');

        return is_string($hash) && $hash !== '' && Hash::check($password, $hash);
    }

    public function setGatePassword(string $password, User $actor): void
    {
        $this->set('security.gate.password_hash', Hash::make($password), $actor, true);
    }

    public function mail(): array
    {
        $storedPassword = $this->get('mail.smtp.password');

        return [
            'host' => (string) $this->get('mail.smtp.host', ''),
            'port' => (int) $this->get('mail.smtp.port', 587),
            'scheme' => (string) $this->get('mail.smtp.scheme', 'smtp'),
            'username' => (string) $this->get('mail.smtp.username', ''),
            'password' => is_string($storedPassword) ? $storedPassword : '',
            'from_address' => (string) $this->get('mail.from.address', ''),
            'from_name' => (string) $this->get('mail.from.name', 'Suba Arch ERP'),
        ];
    }

    public function mailConfigured(): bool
    {
        $mail = $this->mail();

        return $mail['host'] !== ''
            && $mail['port'] > 0
            && in_array($mail['scheme'], ['smtp', 'smtps'], true)
            && $mail['username'] !== ''
            && $mail['password'] !== ''
            && $mail['from_address'] !== '';
    }

    public function applyMailConfiguration(): bool
    {
        if (! $this->mailConfigured()) {
            return false;
        }

        $mail = $this->mail();
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.transport' => 'smtp',
            'mail.mailers.smtp.scheme' => $mail['scheme'],
            'mail.mailers.smtp.host' => $mail['host'],
            'mail.mailers.smtp.port' => $mail['port'],
            'mail.mailers.smtp.username' => $mail['username'],
            'mail.mailers.smtp.password' => $mail['password'],
            'mail.from.address' => $mail['from_address'],
            'mail.from.name' => $mail['from_name'],
        ]);
        Mail::purge('smtp');

        return true;
    }

    public function publicConfiguration(): array
    {
        $mail = $this->mail();
        $storedMailReady = $this->mailConfigured();

        return [
            'otp' => array_merge($this->otp(), [
                'digits' => 6,
                'channel' => 'email',
                'mail_driver' => $storedMailReady ? 'smtp' : config('mail.default'),
                'mail_ready' => $storedMailReady
                    || ! in_array(config('mail.default'), ['log', 'array'], true),
            ]),
            'access_gate' => [
                'enabled' => $this->gateEnabled(),
                'configured' => $this->gateConfigured(),
                'session_hours' => (int) $this->get('security.gate.session_hours', 12),
            ],
            'mail' => [
                'configured' => $storedMailReady,
                'host' => $mail['host'],
                'port' => $mail['port'],
                'scheme' => $mail['scheme'],
                'username' => $mail['username'],
                'password_configured' => $mail['password'] !== '',
                'from_address' => $mail['from_address'],
                'from_name' => $mail['from_name'],
            ],
        ];
    }
}
