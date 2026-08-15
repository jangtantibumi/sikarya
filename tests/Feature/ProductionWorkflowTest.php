<?php

namespace Tests\Feature;

use App\Models\BillOfMaterial;
use App\Models\BomLine;
use App\Models\Company;
use App\Models\CompanyFeature;
use App\Models\CompanyMembership;
use App\Models\Product;
use App\Models\ProductionOrder;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\InventoryLedgerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;
    private User $ceo;
    private User $manager;
    private User $staff;
    private Product $finishedGood;
    private Product $componentA;
    private Product $componentB;
    private BillOfMaterial $bom;
    private Warehouse $warehouse;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::query()->create(['name' => 'Test Corp', 'slug' => 'test-corp']);
        
        CompanyFeature::create(['company_id' => $this->company->id, 'feature_key' => 'production', 'state' => 'active']);
        CompanyFeature::create(['company_id' => $this->company->id, 'feature_key' => 'inventory', 'state' => 'active']);

        $this->ceo = User::factory()->create(['role' => 'ceo', 'company_id' => $this->company->id, 'is_active' => true, 'account_status' => 'active']);
        $this->manager = User::factory()->create(['role' => 'mgr_ops', 'company_id' => $this->company->id, 'is_active' => true, 'account_status' => 'active']);
        $this->staff = User::factory()->create(['role' => 'staff_ops', 'company_id' => $this->company->id, 'is_active' => true, 'account_status' => 'active']);
        
        CompanyMembership::query()->create(['company_id' => $this->company->id, 'user_id' => $this->ceo->id, 'role' => 'ceo', 'is_active' => true]);
        CompanyMembership::query()->create(['company_id' => $this->company->id, 'user_id' => $this->manager->id, 'role' => 'mgr_ops', 'is_active' => true]);
        CompanyMembership::query()->create(['company_id' => $this->company->id, 'user_id' => $this->staff->id, 'role' => 'staff_ops', 'is_active' => true]);

        $this->warehouse = Warehouse::query()->create(['company_id' => $this->company->id, 'code' => 'W-01', 'name' => 'Main']);
        
        $this->finishedGood = Product::query()->create(['company_id' => $this->company->id, 'sku' => 'FG-01', 'name' => 'Sofa']);
        $this->componentA = Product::query()->create(['company_id' => $this->company->id, 'sku' => 'RM-01', 'name' => 'Wood']);
        $this->componentB = Product::query()->create(['company_id' => $this->company->id, 'sku' => 'RM-02', 'name' => 'Fabric']);

        // Give initial stock for components
        $ledger = app(InventoryLedgerService::class);
        $ledger->move($this->componentA, $this->warehouse, 100, 'initial_balance');
        $ledger->move($this->componentB, $this->warehouse, 100, 'initial_balance');

        $this->bom = BillOfMaterial::query()->create(['company_id' => $this->company->id, 'product_id' => $this->finishedGood->id, 'name' => 'Sofa Standard']);
        BomLine::query()->create(['bill_of_material_id' => $this->bom->id, 'component_id' => $this->componentA->id, 'quantity_per_unit' => 2]);
        BomLine::query()->create(['bill_of_material_id' => $this->bom->id, 'component_id' => $this->componentB->id, 'quantity_per_unit' => 3]);
    }

    public function test_store_auto_populates_materials()
    {
        $response = $this->actingAs($this->staff, 'web')->postJson('/api/production/orders', [
            'product_id' => $this->finishedGood->id,
            'bill_of_material_id' => $this->bom->id,
            'planned_quantity' => 10,
        ])->assertStatus(201);

        $this->assertCount(2, $response->json('materials'));
        
        // 10 qty * 2 unit = 20
        $this->assertEquals(20, collect($response->json('materials'))->where('product_id', $this->componentA->id)->first()['planned_quantity']);
        $this->assertEquals('default', collect($response->json('materials'))->first()['status']);
    }

    public function test_staff_modifying_materials_sets_pending_approval()
    {
        $poId = $this->actingAs($this->staff, 'web')->postJson('/api/production/orders', [
            'product_id' => $this->finishedGood->id,
            'bill_of_material_id' => $this->bom->id,
            'planned_quantity' => 1,
        ])->json('id');

        $this->actingAs($this->staff, 'web')->postJson("/api/production/orders/{$poId}/materials/modify", [
            'materials' => [
                ['product_id' => $this->componentA->id, 'actual_quantity' => 5]
            ]
        ])->assertStatus(200);

        $mo = ProductionOrder::with('materials')->find($poId);
        $this->assertCount(1, $mo->materials);
        $this->assertEquals(5, $mo->materials->first()->actual_quantity);
        $this->assertEquals('pending_approval', $mo->materials->first()->status);
    }

    public function test_manager_and_ceo_approval_flow()
    {
        $poId = $this->actingAs($this->staff, 'web')->postJson('/api/production/orders', [
            'product_id' => $this->finishedGood->id,
            'bill_of_material_id' => $this->bom->id,
            'planned_quantity' => 1,
        ])->json('id');

        // Staff changes material
        $this->actingAs($this->staff, 'web')->postJson("/api/production/orders/{$poId}/materials/modify", [
            'materials' => [
                ['product_id' => $this->componentA->id, 'actual_quantity' => 5]
            ]
        ]);

        // Complete fails if not approved
        $this->actingAs($this->staff, 'web')->postJson("/api/production/orders/{$poId}/release");
        $this->actingAs($this->staff, 'web')->postJson("/api/production/orders/{$poId}/complete", [
            'warehouse_id' => $this->warehouse->id, 'quantity' => 1
        ])->assertStatus(422);

        // Manager approves -> becomes manager_approved
        $this->actingAs($this->manager, 'web')->postJson("/api/production/orders/{$poId}/materials/decision", [
            'decision' => 'approve'
        ])->assertStatus(200);

        $this->assertEquals('manager_approved', ProductionOrder::with('materials')->find($poId)->materials->first()->status);

        // Complete still fails
        $this->actingAs($this->staff, 'web')->postJson("/api/production/orders/{$poId}/complete", [
            'warehouse_id' => $this->warehouse->id, 'quantity' => 1
        ])->assertStatus(422);

        // CEO approves -> becomes approved
        $this->actingAs($this->ceo, 'web')->postJson("/api/production/orders/{$poId}/materials/decision", [
            'decision' => 'approve'
        ])->assertStatus(200);

        // Complete now succeeds
        $this->actingAs($this->staff, 'web')->postJson("/api/production/orders/{$poId}/complete", [
            'warehouse_id' => $this->warehouse->id, 'quantity' => 1
        ])->assertStatus(200);

        // Backflushing check: stock of component A should be 100 - 5 = 95
        $this->assertEquals(95, app(InventoryLedgerService::class)->balance($this->componentA, $this->warehouse));
    }

    public function test_ceo_can_bypass_and_directly_edit()
    {
        $poId = $this->actingAs($this->staff, 'web')->postJson('/api/production/orders', [
            'product_id' => $this->finishedGood->id,
            'bill_of_material_id' => $this->bom->id,
            'planned_quantity' => 1,
        ])->json('id');

        // CEO edits directly
        $this->actingAs($this->ceo, 'web')->postJson("/api/production/orders/{$poId}/materials/modify", [
            'materials' => [
                ['product_id' => $this->componentB->id, 'actual_quantity' => 10]
            ]
        ])->assertStatus(200);

        $mo = ProductionOrder::with('materials')->find($poId);
        $this->assertEquals('approved', $mo->materials->first()->status);
        $this->assertEquals($this->ceo->id, $mo->materials->first()->approved_by_id);
    }
}
