<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationChartTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'DataMigrationSeeder']);
    }

    public function test_every_employee_can_read_the_active_company_hierarchy_without_private_fields(): void
    {
        $staff = User::query()->where('username', 'maulana')->firstOrFail();
        $operationsStaff = User::query()->where('username', 'staff_ops')->firstOrFail();

        $response = $this->actingAs($staff)
            ->getJson('/api/organization-chart')
            ->assertOk()
            ->assertJsonPath('scope', 'company_read_only')
            ->assertJsonPath('people.staff_ops.name', $operationsStaff->name)
            ->assertJsonPath('people.staff_ops.division', 'operasional')
            ->assertJsonPath('people.staff_ops.can_view_performance', false)
            ->assertJsonPath('people.maulana.is_self', true)
            ->assertJsonPath('people.maulana.can_view_performance', true);

        $response
            ->assertJsonMissing(['email' => $operationsStaff->email])
            ->assertJsonMissingPath('people.staff_ops.email')
            ->assertJsonMissingPath('people.staff_ops.employee_code')
            ->assertJsonMissingPath('people.staff_ops.attendance')
            ->assertJsonMissingPath('people.staff_ops.kpi');
    }

    public function test_manager_can_only_manage_and_view_performance_for_direct_reports(): void
    {
        $manager = User::query()->where('username', 'mgr_marketing')->firstOrFail();

        $this->actingAs($manager)
            ->getJson('/api/organization-chart')
            ->assertOk()
            ->assertJsonPath('viewer.can_manage_company', false)
            ->assertJsonPath('viewer.can_manage_division', true)
            ->assertJsonPath('people.maulana.can_manage', true)
            ->assertJsonPath('people.maulana.can_view_performance', true)
            ->assertJsonPath('people.staff_ops.can_manage', false)
            ->assertJsonPath('people.staff_ops.can_view_performance', false);
    }

    public function test_inactive_and_archived_people_are_not_exposed_in_the_active_hierarchy(): void
    {
        $staff = User::query()->where('username', 'staff_ops')->firstOrFail();
        $staff->forceFill([
            'is_active' => false,
            'deactivated_at' => now(),
        ])->save();

        $ceo = User::query()->where('username', 'ceo')->firstOrFail();

        $this->actingAs($ceo)
            ->getJson('/api/organization-chart')
            ->assertOk()
            ->assertJsonMissingPath('people.staff_ops');
    }

    public function test_company_hierarchy_requires_an_authenticated_session(): void
    {
        $this->getJson('/api/organization-chart')
            ->assertUnauthorized();
    }
}
