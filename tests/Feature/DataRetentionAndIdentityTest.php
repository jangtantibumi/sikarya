<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use App\Services\SecuritySettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class DataRetentionAndIdentityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->artisan('db:seed', ['--class' => 'DataMigrationSeeder']);
    }

    public function test_employee_identity_is_generated_from_division_level_name_and_sequence(): void
    {
        $ceo = User::query()->where('username', 'ceo')->firstOrFail();

        $this->actingAs($ceo)
            ->postJson('/api/users/identity-preview', [
                'name' => 'Budi Santoso',
                'role' => 'staff_marketing',
                'employment_type' => 'Full-Time',
            ])
            ->assertOk()
            ->assertJsonPath('identity.username', fn (string $username): bool => (bool) preg_match(
                '/^sa\.mkt\.stf\.budi-santoso\.\d{4}$/',
                $username,
            ))
            ->assertJsonPath('identity.employee_code', fn (string $code): bool => (bool) preg_match(
                '/^SA-MKT-STF-\d{4}$/',
                $code,
            ));

        $created = $this->actingAs($ceo)
            ->postJson('/api/users', [
                'name' => 'Budi Santoso',
                'email' => 'budi.santoso@example.com',
                'role' => 'staff_marketing',
                'job_title' => 'Content Creator',
                'parent' => 'mgr_marketing',
                'employment_type' => 'Full-Time',
            ])
            ->assertCreated();

        $username = $created->json('user.username');
        $employeeCode = $created->json('user.employee_code');

        $this->assertMatchesRegularExpression(
            '/^sa\.mkt\.stf\.budi-santoso\.\d{4}$/',
            $username,
        );
        $this->assertMatchesRegularExpression('/^SA-MKT-STF-\d{4}$/', $employeeCode);
        $this->assertDatabaseHas('users', [
            'name' => 'Budi Santoso',
            'username' => $username,
            'employee_code' => $employeeCode,
            'job_title' => 'Content Creator',
        ]);
    }

    public function test_deactivation_removes_employee_from_active_views_but_retains_history_and_archives_it(): void
    {
        $ceo = User::query()->where('username', 'ceo')->firstOrFail();
        $staff = User::query()->where('username', 'maulana')->firstOrFail();
        $attendance = Attendance::query()->create([
            'user_id' => $staff->id,
            'clock_in' => now()->subHours(8),
            'clock_out' => now(),
            'status' => 'present',
            'work_type' => 'WFO',
        ]);

        $this->actingAs($ceo)
            ->deleteJson("/api/users/{$staff->username}", [
                'completion_status' => 'completed',
                'separation_reason' => 'completed',
                'separation_notes' => 'Seluruh pekerjaan telah diserahterimakan.',
                'effective_date' => now()->toDateString(),
            ])
            ->assertOk();

        $staff->refresh();
        $this->assertFalse($staff->is_active);
        $this->assertNotNull($staff->deactivated_at);
        $this->assertDatabaseHas('attendances', ['id' => $attendance->id]);

        $this->actingAs($ceo)
            ->getJson('/api/users')
            ->assertOk()
            ->assertJsonMissingPath($staff->username);

        $staff->forceFill(['deactivated_at' => now()->subDays(31)])->save();
        $this->actingAs($ceo)
            ->postJson('/api/admin/retention/run')
            ->assertOk()
            ->assertJsonPath('metrics.archived', 1)
            ->assertJsonPath('metrics.anonymized', 0)
            ->assertJsonPath('metrics.purged', 0);

        $this->assertNotNull($staff->fresh()->archived_at);
        $this->assertDatabaseHas('attendances', ['id' => $attendance->id]);
    }

    public function test_legal_hold_protects_old_employee_data_when_automatic_cleanup_is_enabled(): void
    {
        $ceo = User::query()->where('username', 'ceo')->firstOrFail();
        $staff = User::query()->where('username', 'maulana')->firstOrFail();
        $attendance = Attendance::query()->create([
            'user_id' => $staff->id,
            'clock_in' => now()->subYears(3),
            'clock_out' => now()->subYears(3)->addHours(8),
            'status' => 'present',
            'work_type' => 'WFO',
        ]);
        $attendance->delete();
        $attendance->forceFill(['deleted_at' => now()->subYears(3)])->save();

        $staff->forceFill([
            'is_active' => false,
            'deactivated_at' => now()->subYears(8),
            'legal_hold' => true,
        ])->save();

        $settings = app(SecuritySettingsService::class);
        $settings->set('retention.auto_anonymize', true, $ceo);
        $settings->set('retention.auto_purge', true, $ceo);

        $this->actingAs($ceo)
            ->postJson('/api/admin/retention/run')
            ->assertOk()
            ->assertJsonPath('metrics.anonymized', 0)
            ->assertJsonPath('metrics.purged', 0);

        $this->assertNull($staff->fresh()->anonymized_at);
        $this->assertDatabaseHas('users', [
            'id' => $staff->id,
            'name' => $staff->name,
            'legal_hold' => true,
        ]);
        $this->assertDatabaseHas('attendances', ['id' => $attendance->id]);
    }
}
