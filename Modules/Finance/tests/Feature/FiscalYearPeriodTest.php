<?php

declare(strict_types=1);

namespace Modules\Finance\Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Finance\Models\FiscalYear;
use Tests\TestCase;

class FiscalYearPeriodTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create();
        $this->user = User::factory()->create([
            'company_id' => $this->company->id,
            'is_active' => true,
        ]);
        $this->actingAs($this->user);
    }

    public function test_can_create_fiscal_year_and_auto_generates_periods(): void
    {
        $payload = [
            'code' => 'FY2026',
            'name' => 'Tahun Buku 2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
        ];

        $response = $this->postJson(route('finance.fiscal-years.store'), $payload);

        $response->assertStatus(201)
            ->assertJsonFragment(['code' => 'FY2026']);

        $fy = FiscalYear::where('company_id', $this->company->id)->where('code', 'FY2026')->first();
        $this->assertNotNull($fy);
        $this->assertEquals(12, $fy->periods()->count());
    }
}
