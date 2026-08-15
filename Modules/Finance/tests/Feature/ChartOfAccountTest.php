<?php

declare(strict_types=1);

namespace Modules\Finance\Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Finance\Models\AccountGroup;
use Modules\Finance\Models\ChartOfAccount;
use Tests\TestCase;

class ChartOfAccountTest extends TestCase
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

    public function font_can_list_chart_of_accounts(): void
    {
        ChartOfAccount::factory()->create(['company_id' => $this->company->id, 'code' => '1110.01']);

        $response = $this->getJson(route('finance.chart-of-accounts.index'));

        $response->assertStatus(200)
            ->assertJsonFragment(['code' => '1110.01']);
    }

    public function test_can_create_chart_of_account(): void
    {
        $group = AccountGroup::factory()->create(['company_id' => $this->company->id]);

        $payload = [
            'account_group_id' => $group->id,
            'code' => '1110.02',
            'name' => 'Bank BCA IDR',
            'type' => 'asset',
            'balance_type' => 'debit',
            'is_header' => false,
            'is_reconciliation' => true,
        ];

        $response = $this->postJson(route('finance.chart-of-accounts.store'), $payload);

        $response->assertStatus(201)
            ->assertJsonFragment(['code' => '1110.02']);

        $this->assertDatabaseHas('finance_chart_of_accounts', [
            'company_id' => $this->company->id,
            'code' => '1110.02',
            'name' => 'Bank BCA IDR',
        ]);
    }
}
