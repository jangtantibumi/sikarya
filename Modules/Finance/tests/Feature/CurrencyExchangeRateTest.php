<?php

namespace Modules\Finance\Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Finance\Models\Currency;
use Tests\TestCase;

class CurrencyExchangeRateTest extends TestCase
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

    public function test_can_create_currency_and_exchange_rate(): void
    {
        $idr = Currency::factory()->create(['company_id' => $this->company->id, 'code' => 'IDR', 'is_base' => true]);
        $usd = Currency::factory()->create(['company_id' => $this->company->id, 'code' => 'USD', 'is_base' => false]);

        $payload = [
            'from_currency_id' => $usd->id,
            'to_currency_id' => $idr->id,
            'rate_date' => '2026-08-06',
            'rate_type' => 'spot',
            'rate' => 15850.500000,
        ];

        $response = $this->postJson(route('finance.exchange-rates.store'), $payload);

        $response->assertStatus(201);
        $this->assertDatabaseHas('finance_exchange_rates', [
            'company_id' => $this->company->id,
            'from_currency_id' => $usd->id,
            'to_currency_id' => $idr->id,
        ]);
    }
}
