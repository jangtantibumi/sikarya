<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\CompanyMembership;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\InventoryLedgerService;
use App\Services\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchasingWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->company = Company::query()->create(['name' => 'Demo Corp', 'slug' => 'demo-corp']);
        $this->otherCompany = Company::query()->create(['name' => 'Other Corp', 'slug' => 'other-corp']);
        
        $this->ceo = User::factory()->create(['role' => 'ceo', 'company_id' => $this->company->id, 'is_active' => true, 'account_status' => 'active']);
        $this->manager = User::factory()->create(['role' => 'mgr_ops', 'company_id' => $this->company->id, 'is_active' => true, 'account_status' => 'active']);
        $this->staff = User::factory()->create(['role' => 'staff_ops', 'company_id' => $this->company->id, 'is_active' => true, 'account_status' => 'active']);
        
        CompanyMembership::query()->create(['company_id' => $this->company->id, 'user_id' => $this->ceo->id, 'role' => 'ceo', 'is_active' => true]);
        CompanyMembership::query()->create(['company_id' => $this->company->id, 'user_id' => $this->manager->id, 'role' => 'mgr_ops', 'is_active' => true]);
        CompanyMembership::query()->create(['company_id' => $this->company->id, 'user_id' => $this->staff->id, 'role' => 'staff_ops', 'is_active' => true]);

        $this->supplier = Supplier::query()->create(['company_id' => $this->company->id, 'code' => 'SUP-01', 'name' => 'Supplier A', 'email' => 'sup@example.com']);
        $this->product = Product::query()->create(['company_id' => $this->company->id, 'sku' => 'SKU-01', 'name' => 'Laptop']);
        $this->warehouse = Warehouse::query()->create(['company_id' => $this->company->id, 'code' => 'MAIN', 'name' => 'Main Warehouse']);
        
        \App\Models\CompanyFeature::create(['company_id' => $this->company->id, 'feature_key' => 'purchasing', 'state' => 'active']);
        \App\Models\CompanyFeature::create(['company_id' => $this->company->id, 'feature_key' => 'inventory', 'state' => 'active']);
        
        app(TenantContext::class)->setCompany($this->company);
        
        \App\Models\Account::create(['company_id' => $this->company->id, 'code' => '1-100', 'name' => 'Inventory', 'type' => 'asset', 'normal_balance' => 'debit', 'system_key' => 'inventory', 'is_active' => true]);
        \App\Models\Account::create(['company_id' => $this->company->id, 'code' => '2-100', 'name' => 'Accounts Payable', 'type' => 'liability', 'normal_balance' => 'credit', 'system_key' => 'accounts_payable', 'is_active' => true]);
    }

    public function test_self_approval_is_rejected(): void
    {
        $response = $this->actingAs($this->manager, 'web')
            ->postJson('/api/purchasing/orders', [
                'supplier_id' => $this->supplier->id,
                'lines' => [
                    ['product_id' => $this->product->id, 'quantity' => 2, 'unit_price' => 1000000]
                ]
            ]);
        $response->assertStatus(201);
        $poId = $response->json('id');
        
        $this->actingAs($this->manager, 'web')
            ->postJson("/api/purchasing/orders/{$poId}/submit")
            ->assertOk();
            
        $this->actingAs($this->manager, 'web')
            ->postJson("/api/purchasing/orders/{$poId}/decision", [
                'decision' => 'approved'
            ])->assertForbidden();
    }

    public function test_manager_can_approve_po_under_10_million(): void
    {
        $response = $this->actingAs($this->staff, 'web')
            ->postJson('/api/purchasing/orders', [
                'supplier_id' => $this->supplier->id,
                'lines' => [
                    ['product_id' => $this->product->id, 'quantity' => 5, 'unit_price' => 1000000]
                ]
            ]);
        $response->assertStatus(201);
        $poId = $response->json('id');
        
        $this->actingAs($this->staff, 'web')
            ->postJson("/api/purchasing/orders/{$poId}/submit")
            ->assertOk();
            
        $this->actingAs($this->manager, 'web')
            ->postJson("/api/purchasing/orders/{$poId}/decision", [
                'decision' => 'approved'
            ])->assertOk()->assertJsonPath('status', 'approved');
    }

    public function test_ceo_must_approve_po_over_10_million(): void
    {
        $response = $this->actingAs($this->staff, 'web')
            ->postJson('/api/purchasing/orders', [
                'supplier_id' => $this->supplier->id,
                'lines' => [
                    ['product_id' => $this->product->id, 'quantity' => 15, 'unit_price' => 1000000]
                ]
            ]);
        $response->assertStatus(201);
        $poId = $response->json('id');
        
        $this->actingAs($this->staff, 'web')
            ->postJson("/api/purchasing/orders/{$poId}/submit")
            ->assertOk();
            
        $this->actingAs($this->manager, 'web')
            ->postJson("/api/purchasing/orders/{$poId}/decision", [
                'decision' => 'approved'
            ])->assertForbidden();
            
        $this->actingAs($this->ceo, 'web')
            ->postJson("/api/purchasing/orders/{$poId}/decision", [
                'decision' => 'approved'
            ])->assertOk()->assertJsonPath('status', 'approved');
    }

    public function test_goods_receipt_partial_and_full_and_over_receipt(): void
    {
        // 1. Create and Approve PO
        $response = $this->actingAs($this->staff, 'web')
            ->postJson('/api/purchasing/orders', [
                'supplier_id' => $this->supplier->id,
                'lines' => [
                    ['product_id' => $this->product->id, 'quantity' => 5, 'unit_price' => 1000000]
                ]
            ]);
        $response->assertCreated();
        $poId = $response->json('id');
        $lineId = $response->json('lines.0.id');
        
        $this->actingAs($this->staff, 'web')->postJson("/api/purchasing/orders/{$poId}/submit")->assertOk();
        $this->actingAs($this->manager, 'web')->postJson("/api/purchasing/orders/{$poId}/decision", ['decision' => 'approved'])->assertOk();
        
        // 2. Partial Receipt
        $this->actingAs($this->staff, 'web')
            ->postJson("/api/purchasing/goods-receipts", [
                'purchase_order_id' => $poId,
                'warehouse_id' => $this->warehouse->id,
                'lines' => [
                    ['purchase_order_line_id' => $lineId, 'quantity' => 2]
                ]
            ])->assertStatus(201);
            
        $this->assertSame(2.0, app(InventoryLedgerService::class)->balance($this->product, $this->warehouse));
        $this->assertSame('partially_received', PurchaseOrder::find($poId)->status);
        
        // 3. Over-receipt Rejected
        $this->travel(1)->seconds();
        $this->actingAs($this->staff, 'web')
            ->postJson("/api/purchasing/goods-receipts", [
                'purchase_order_id' => $poId,
                'warehouse_id' => $this->warehouse->id,
                'lines' => [
                    ['purchase_order_line_id' => $lineId, 'quantity' => 4]
                ]
            ])->assertStatus(422);
            
        // 4. Full Receipt
        $this->travel(1)->seconds();
        $this->actingAs($this->staff, 'web')
            ->postJson("/api/purchasing/goods-receipts", [
                'purchase_order_id' => $poId,
                'warehouse_id' => $this->warehouse->id,
                'lines' => [
                    ['purchase_order_line_id' => $lineId, 'quantity' => 3]
                ]
            ])->assertStatus(201);
            
        $this->assertSame(5.0, app(InventoryLedgerService::class)->balance($this->product, $this->warehouse));
        $this->assertSame('received', PurchaseOrder::find($poId)->status);
    }
    
    public function test_tenant_isolation(): void
    {
        $response = $this->actingAs($this->staff, 'web')
            ->postJson('/api/purchasing/orders', [
                'supplier_id' => $this->supplier->id,
                'lines' => [
                    ['product_id' => $this->product->id, 'quantity' => 1, 'unit_price' => 1000]
                ]
            ]);
        $poId = $response->json('id');
        
        $otherStaff = User::factory()->create(['role' => 'staff_ops', 'company_id' => $this->otherCompany->id, 'is_active' => true, 'account_status' => 'active']);
        CompanyMembership::query()->create(['company_id' => $this->otherCompany->id, 'user_id' => $otherStaff->id, 'role' => 'staff_ops', 'is_active' => true]);
        
        \App\Models\CompanyFeature::create(['company_id' => $this->otherCompany->id, 'feature_key' => 'purchasing', 'state' => 'active']);
        \App\Models\CompanyFeature::create(['company_id' => $this->otherCompany->id, 'feature_key' => 'inventory', 'state' => 'active']);
        
        app(TenantContext::class)->setCompany($this->otherCompany);
        
        $this->actingAs($otherStaff, 'web')
            ->getJson("/api/purchasing/orders")
            ->assertOk()
            ->assertJsonCount(0);
            
        $this->actingAs($otherStaff, 'web')
            ->postJson("/api/purchasing/orders/{$poId}/submit")
            ->assertNotFound(); // Assuming TenantScope hides it, or 403
    }
}
