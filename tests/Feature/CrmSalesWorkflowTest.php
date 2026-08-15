<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\CompanyFeature;
use App\Models\CompanyMembership;
use App\Models\Lead;
use App\Models\Product;
use App\Models\User;
use App\Services\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrmSalesWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;
    private User $ceo;
    private User $marketing;
    private Product $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::query()->create(['name' => 'CRM Corp', 'slug' => 'crm-corp']);
        
        CompanyFeature::create(['company_id' => $this->company->id, 'feature_key' => 'crm', 'state' => 'active']);
        CompanyFeature::create(['company_id' => $this->company->id, 'feature_key' => 'inventory', 'state' => 'active']);

        $this->ceo = User::factory()->create(['role' => 'ceo', 'company_id' => $this->company->id, 'is_active' => true, 'account_status' => 'active']);
        $this->marketing = User::factory()->create(['role' => 'mgr_marketing', 'company_id' => $this->company->id, 'is_active' => true, 'account_status' => 'active']);
        
        CompanyMembership::query()->create(['company_id' => $this->company->id, 'user_id' => $this->ceo->id, 'role' => 'ceo', 'is_active' => true]);
        CompanyMembership::query()->create(['company_id' => $this->company->id, 'user_id' => $this->marketing->id, 'role' => 'mgr_marketing', 'is_active' => true]);

        $this->service = Product::query()->create(['company_id' => $this->company->id, 'sku' => 'SRV-01', 'name' => 'Jasa Desain']);

        app(TenantContext::class)->setCompany($this->company);
    }

    public function test_crm_sales_quotation_workflow(): void
    {
        // 1. Create Lead
        $lead = Lead::query()->create([
            'company_id' => $this->company->id,
            'client_name' => 'Bapak Budi',
            'phone' => '628123456789',
            'status' => 'leads',
            'assigned_to' => $this->marketing->id,
            'created_by' => $this->marketing->id,
        ]);

        // 2. Create Quotation
        $response = $this->actingAs($this->marketing, 'web')->postJson("/api/crm/leads/{$lead->id}/quotations", [
            'lines' => [
                [
                    'product_id' => $this->service->id,
                    'description' => 'Desain Interior',
                    'quantity' => 1,
                    'unit_price' => 15000000
                ]
            ]
        ])->assertStatus(201);

        $quotationId = $response->json('id');
        
        // Lead should now be in 'penawaran' status
        $this->assertEquals('penawaran', $lead->refresh()->status);
        $this->assertEquals(15000000, $lead->project_value);

        // 3. Send via WhatsApp
        $this->actingAs($this->marketing, 'web')
            ->postJson("/api/crm/quotations/{$quotationId}/send-whatsapp")
            ->assertOk()
            ->assertJsonPath('message', 'Quotation dikirim via WhatsApp.');

        // Quotation should be 'sent'
        $this->assertEquals('sent', \App\Models\SalesQuotation::find($quotationId)->status);

        // 4. Accept Quotation
        $this->actingAs($this->marketing, 'web')
            ->postJson("/api/crm/quotations/{$quotationId}/accept")
            ->assertOk();

        // Lead should be 'deal'
        $this->assertEquals('deal', $lead->refresh()->status);
        $this->assertNotNull($lead->won_at);
        $this->assertEquals('accepted', \App\Models\SalesQuotation::find($quotationId)->status);
    }
}
