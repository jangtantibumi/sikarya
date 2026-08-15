<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\CompanyFeature;
use App\Models\CompanyMembership;
use App\Models\Lead;
use App\Models\Product;
use App\Models\SalesQuotation;
use App\Models\User;
use App\Models\WebhookSubscription;
use App\Services\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdvancedAnalyticsAndIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;
    private User $ceo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::query()->create(['name' => 'Tech Corp', 'slug' => 'tech-corp']);
        
        CompanyFeature::create(['company_id' => $this->company->id, 'feature_key' => 'intelligence', 'state' => 'active']);
        CompanyFeature::create(['company_id' => $this->company->id, 'feature_key' => 'crm', 'state' => 'active']);

        $this->ceo = User::factory()->create(['role' => 'ceo', 'company_id' => $this->company->id, 'is_active' => true, 'account_status' => 'active']);
        CompanyMembership::query()->create(['company_id' => $this->company->id, 'user_id' => $this->ceo->id, 'role' => 'ceo', 'is_active' => true]);

        app(TenantContext::class)->setCompany($this->company);
    }

    public function test_advanced_analytics_contains_cross_module_metrics(): void
    {
        // Generate some data
        $lead = Lead::query()->create([
            'company_id' => $this->company->id,
            'client_name' => 'Demo Client',
            'status' => 'deal',
            'project_value' => 50000000,
            'assigned_to' => $this->ceo->id,
            'created_by' => $this->ceo->id,
        ]);

        $this->withoutExceptionHandling();
        $response = $this->actingAs($this->ceo, 'web')
            ->getJson('/api/analytics/overview')
            ->assertOk();

        // Asset cross-module keys
        $response->assertJsonStructure([
            'crm' => ['total_leads', 'open_pipeline_value', 'won_value', 'conversion_rate'],
            'purchasing',
            'inventory',
            'production' => ['defect_rate'],
        ]);

        $this->assertEquals(50000000, $response->json('crm.won_value'));
    }

    public function test_webhook_is_dispatched_on_deal(): void
    {
        Http::fake();

        WebhookSubscription::query()->create([
            'company_id' => $this->company->id,
            'event_name' => 'lead.deal',
            'url' => 'https://example.com/webhook',
            'is_active' => true,
        ]);

        $lead = Lead::query()->create([
            'company_id' => $this->company->id,
            'client_name' => 'Webhook Client',
            'status' => 'penawaran',
            'assigned_to' => $this->ceo->id,
            'created_by' => $this->ceo->id,
        ]);

        $quotation = SalesQuotation::query()->create([
            'company_id' => $this->company->id,
            'lead_id' => $lead->id,
            'number' => 'SQ-123',
            'date' => today(),
            'status' => 'sent',
            'total_amount' => 100000,
            'created_by_id' => $this->ceo->id,
        ]);

        $this->actingAs($this->ceo, 'web')
            ->postJson("/api/crm/quotations/{$quotation->id}/accept")
            ->assertOk();

        Http::assertSent(function (\Illuminate\Http\Client\Request $request) {
            return $request->url() == 'https://example.com/webhook' &&
                   $request['event'] == 'lead.deal' &&
                   $request['data']['client_name'] == 'Webhook Client';
        });
    }
}
