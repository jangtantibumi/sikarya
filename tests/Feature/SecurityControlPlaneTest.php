<?php

namespace Tests\Feature;

use App\Mail\LoginOtpMail;
use App\Models\AuditEvent;
use App\Models\FeatureFlag;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\SecuritySettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SecurityControlPlaneTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->artisan('db:seed', ['--class' => 'DataMigrationSeeder']);
    }

    public function test_otp_is_emailed_hashed_one_time_and_never_returned_by_api(): void
    {
        Mail::fake();
        $user = User::query()->where('username', 'maulana')->firstOrFail();

        $sent = $this->postJson('/api/login/send-otp', [
            'username' => $user->username,
        ]);

        $sent
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('otp_digits', 6);

        $this->assertArrayNotHasKey('debug_otp', $sent->json());
        $this->assertArrayNotHasKey('otp', $sent->json());

        $otp = null;
        Mail::assertSent(LoginOtpMail::class, function (LoginOtpMail $mail) use ($user, &$otp): bool {
            $otp = $mail->otp;

            return $mail->hasTo($user->email);
        });

        $this->assertMatchesRegularExpression('/^\d{6}$/', (string) $otp);
        $rawHash = DB::table('users')->where('id', $user->id)->value('otp_code');
        $this->assertNotSame($otp, $rawHash);
        $this->assertTrue(Hash::check((string) $otp, (string) $rawHash));

        $this->postJson('/api/login/verify-otp', [
            'username' => $user->username,
            'otp' => $otp,
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonMissingPath('user.password')
            ->assertJsonMissingPath('user.otp_code');

        $this->assertAuthenticatedAs($user);
        $this->assertNull($user->fresh()->otp_code);

        $this->postJson('/api/login/verify-otp', [
            'username' => $user->username,
            'otp' => $otp,
        ])->assertUnprocessable();

        $this->assertDatabaseHas('audit_events', [
            'actor_id' => $user->id,
            'event_type' => 'auth.login_succeeded',
        ]);
    }

    public function test_unknown_account_response_is_generic_and_legacy_master_code_cannot_login(): void
    {
        Mail::fake();

        $this->postJson('/api/login/send-otp', [
            'username' => 'akun-yang-tidak-ada',
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonMissingPath('exists')
            ->assertJsonMissingPath('debug_otp');

        Mail::assertNothingSent();

        $this->postJson('/api/login/verify-otp', [
            'username' => 'maulana',
            'otp' => '1234',
        ])->assertUnprocessable();

        $this->assertGuest();
        $this->assertDatabaseHas('audit_events', [
            'event_type' => 'otp.request_unknown',
        ]);
    }

    public function test_otp_is_locked_after_the_ceo_configured_maximum_attempts(): void
    {
        $ceo = User::query()->where('username', 'ceo')->firstOrFail();
        $user = User::query()->where('username', 'maulana')->firstOrFail();
        $settings = app(SecuritySettingsService::class);
        $settings->set('security.otp.max_attempts', 3, $ceo);
        $settings->set('security.otp.lock_minutes', 20, $ceo);

        $user->forceFill([
            'otp_code' => Hash::make('654321'),
            'otp_expires_at' => now()->addMinutes(5),
            'otp_attempts' => 0,
        ])->save();

        for ($attempt = 1; $attempt <= 3; $attempt++) {
            $this->postJson('/api/login/verify-otp', [
                'username' => $user->username,
                'otp' => '000000',
            ])->assertUnprocessable();
        }

        $user->refresh();
        $this->assertSame(3, $user->otp_attempts);
        $this->assertTrue($user->otp_locked_until->isFuture());
        $this->assertNull($user->otp_code);

        $this->postJson('/api/login/verify-otp', [
            'username' => $user->username,
            'otp' => '654321',
        ])->assertUnprocessable();

        $this->assertGuest();
        $this->assertDatabaseHas('audit_events', [
            'actor_id' => $user->id,
            'event_type' => 'otp.account_locked',
        ]);
    }

    public function test_ceo_controls_available_modules_but_cannot_disable_core_or_enable_unbuilt_roadmap(): void
    {
        $ceo = User::query()->where('username', 'ceo')->firstOrFail();
        $staff = User::query()->where('username', 'maulana')->firstOrFail();

        $this->actingAs($ceo)
            ->putJson('/api/admin/features/crm', ['enabled' => false])
            ->assertOk()
            ->assertJsonPath('feature.enabled', false);

        $this->assertDatabaseHas('feature_flags', [
            'key' => 'crm',
            'is_enabled' => false,
            'updated_by' => $ceo->id,
        ]);

        $this->actingAs($ceo)
            ->getJson('/api/crm/leads')
            ->assertForbidden()
            ->assertJsonPath('code', 'FEATURE_DISABLED');

        $this->actingAs($staff)
            ->putJson('/api/admin/features/crm', ['enabled' => true])
            ->assertForbidden();

        $this->actingAs($ceo)
            ->putJson('/api/admin/features/core_dashboard', ['enabled' => false])
            ->assertUnprocessable();

        $this->actingAs($ceo)
            ->putJson('/api/admin/features/inventory', ['enabled' => true])
            ->assertUnprocessable();
    }

    public function test_company_access_gate_is_required_before_dashboard_and_api_when_enabled(): void
    {
        $ceo = User::query()->where('username', 'ceo')->firstOrFail();
        $settings = app(SecuritySettingsService::class);
        $password = 'SubaArch#ERP2026';

        $settings->setGatePassword($password, $ceo);
        $settings->set('security.gate.enabled', true, $ceo);
        $settings->set('security.gate.session_hours', 8, $ceo);

        $this->get('/')->assertRedirect('/erp-access');

        $this->post('/erp-access', [
            'access_password' => 'Password#Salah2026',
        ])->assertSessionHasErrors('access_password');

        $this->post('/erp-access', [
            'access_password' => $password,
        ])->assertRedirect('/');

        $this->get('/')->assertOk();

        $this->flushSession();
        $this->postJson('/api/login/send-otp', [
            'username' => 'maulana',
        ])
            ->assertStatus(423)
            ->assertJsonPath('code', 'ERP_GATE_REQUIRED');
    }

    public function test_only_ceo_can_change_security_policy_and_gate_secret_is_never_returned(): void
    {
        $staff = User::query()->where('username', 'maulana')->firstOrFail();
        $ceo = User::query()->where('username', 'ceo')->firstOrFail();
        $password = 'Erp-SubaArch#2026';

        $this->actingAs($staff)
            ->putJson('/api/admin/security', [
                'otp_expires_minutes' => 5,
                'otp_resend_seconds' => 60,
                'otp_max_attempts' => 5,
                'otp_lock_minutes' => 15,
                'gate_enabled' => false,
                'gate_session_hours' => 12,
            ])
            ->assertForbidden();

        $rotated = $this->actingAs($ceo)
            ->putJson('/api/admin/security/gate-password', [
                'password' => $password,
                'password_confirmation' => $password,
                'enable_gate' => false,
            ]);

        $rotated
            ->assertOk()
            ->assertJsonPath('security.access_gate.configured', true)
            ->assertJsonMissingPath('password')
            ->assertJsonMissingPath('security.access_gate.password')
            ->assertDontSee($password);

        $rawValue = DB::table('system_settings')
            ->where('key', 'security.gate.password_hash')
            ->value('value');
        $this->assertNotSame($password, $rawValue);
        $this->assertStringNotContainsString($password, (string) $rawValue);
        $this->assertTrue(app(SecuritySettingsService::class)->verifyGatePassword($password));

        $this->actingAs($ceo)
            ->getJson('/api/admin/control-center')
            ->assertOk()
            ->assertJsonMissingPath('security.access_gate.password')
            ->assertDontSee($password);

        $this->assertTrue(
            SystemSetting::query()
                ->where('key', 'security.gate.password_hash')
                ->firstOrFail()
                ->is_secret,
        );
    }

    public function test_ceo_can_store_smtp_credentials_encrypted_without_exposing_the_app_password(): void
    {
        $ceo = User::query()->where('username', 'ceo')->firstOrFail();
        $staff = User::query()->where('username', 'maulana')->firstOrFail();
        $appPassword = 'abcdefghijklmnop';
        $payload = [
            'host' => 'smtp.gmail.com',
            'port' => 587,
            'scheme' => 'smtp',
            'username' => 'suba.arch.crm@gmail.com',
            'password' => $appPassword,
            'from_address' => 'suba.arch.crm@gmail.com',
            'from_name' => 'Suba Arch ERP',
        ];

        $this->actingAs($staff)
            ->putJson('/api/admin/security/mail', $payload)
            ->assertForbidden();

        $saved = $this->actingAs($ceo)
            ->putJson('/api/admin/security/mail', $payload);

        $saved
            ->assertOk()
            ->assertJsonPath('security.mail.configured', true)
            ->assertJsonPath('security.mail.username', 'suba.arch.crm@gmail.com')
            ->assertJsonPath('security.mail.password_configured', true)
            ->assertJsonMissingPath('security.mail.password')
            ->assertDontSee($appPassword);

        $rawPassword = DB::table('system_settings')
            ->where('key', 'mail.smtp.password')
            ->value('value');
        $this->assertNotSame($appPassword, $rawPassword);
        $this->assertStringNotContainsString($appPassword, (string) $rawPassword);
        $this->assertSame($appPassword, app(SecuritySettingsService::class)->mail()['password']);

        $this->actingAs($ceo)
            ->getJson('/api/admin/control-center')
            ->assertOk()
            ->assertJsonMissingPath('security.mail.password')
            ->assertDontSee($appPassword);

        $this->assertTrue(
            SystemSetting::query()
                ->where('key', 'mail.smtp.password')
                ->firstOrFail()
                ->is_secret,
        );
    }

    public function test_user_directory_is_limited_to_the_viewers_authorized_scope(): void
    {
        $marketingManager = User::query()->where('username', 'mgr_marketing')->firstOrFail();
        $marketingStaff = User::query()->where('username', 'maulana')->firstOrFail();

        $this->actingAs($marketingManager)
            ->getJson('/api/users')
            ->assertOk()
            ->assertJsonFragment(['username' => 'maulana'])
            ->assertJsonMissing(['username' => 'staff_ops'])
            ->assertJsonMissing(['username' => 'staff_finance']);

        $this->actingAs($marketingStaff)
            ->getJson('/api/users')
            ->assertOk()
            ->assertJsonFragment(['username' => 'maulana'])
            ->assertJsonFragment(['username' => 'mgr_marketing'])
            ->assertJsonMissing(['username' => 'dbest'])
            ->assertJsonMissing(['username' => 'staff_ops']);
    }

    public function test_audit_events_form_an_integrity_chain_without_storing_secrets(): void
    {
        $ceo = User::query()->where('username', 'ceo')->firstOrFail();
        $password = 'Audit-SubaArch#2026';

        $this->actingAs($ceo)
            ->putJson('/api/admin/security/gate-password', [
                'password' => $password,
                'password_confirmation' => $password,
                'enable_gate' => false,
            ])
            ->assertOk();

        $this->actingAs($ceo)
            ->putJson('/api/admin/features/backup', ['enabled' => false])
            ->assertOk();

        $events = AuditEvent::query()->orderBy('id')->get();
        $this->assertGreaterThanOrEqual(2, $events->count());

        for ($index = 1; $index < $events->count(); $index++) {
            $this->assertSame($events[$index - 1]->event_hash, $events[$index]->previous_hash);
        }

        $serialized = $events->toJson();
        $this->assertStringNotContainsString($password, $serialized);
        $this->assertStringNotContainsString('password_confirmation', $serialized);
        $this->assertSame(
            FeatureFlag::query()->where('key', 'backup')->value('updated_by'),
            $ceo->id,
        );
    }
}
