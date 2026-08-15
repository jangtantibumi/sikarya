<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\CompanyFeature;
use App\Models\CompanyMembership;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderLine;
use App\Models\PosSession;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\AccountingService;
use App\Models\JournalEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class FinanceAutoJournalTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;
    private User $ceo;
    private Warehouse $warehouse;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::query()->create(['name' => 'Finance Corp', 'slug' => 'finance-corp']);
        
        CompanyFeature::create(['company_id' => $this->company->id, 'feature_key' => 'purchasing', 'state' => 'active']);
        CompanyFeature::create(['company_id' => $this->company->id, 'feature_key' => 'procurement', 'state' => 'active']);
        CompanyFeature::create(['company_id' => $this->company->id, 'feature_key' => 'inventory', 'state' => 'active']);
        CompanyFeature::create(['company_id' => $this->company->id, 'feature_key' => 'accounting', 'state' => 'active']);
        CompanyFeature::create(['company_id' => $this->company->id, 'feature_key' => 'pos', 'state' => 'active']);

        $this->ceo = User::factory()->create(['role' => 'ceo', 'company_id' => $this->company->id, 'is_active' => true, 'account_status' => 'active']);
        CompanyMembership::query()->create(['company_id' => $this->company->id, 'user_id' => $this->ceo->id, 'role' => 'ceo', 'is_active' => true]);

        $this->warehouse = Warehouse::query()->create(['company_id' => $this->company->id, 'code' => 'W-F1', 'name' => 'Main Finance']);
        $this->product = Product::query()->create(['company_id' => $this->company->id, 'sku' => 'P-FIN-1', 'name' => 'Item X', 'standard_cost' => 100]);

        app(\App\Services\TenantContext::class)->setCompany($this->company);

        // Seed Accounts
        Artisan::call('app:seed-tenant-accounts');
    }

    public function test_goods_receipt_creates_auto_journal()
    {
        $supplier = \App\Models\Supplier::query()->create([
            'company_id' => $this->company->id,
            'code' => 'SUP-01',
            'name' => 'Supplier A'
        ]);

        $po = PurchaseOrder::query()->create([
            'company_id' => $this->company->id,
            'supplier_id' => $supplier->id,
            'number' => 'PO-001',
            'order_date' => today(),
            'status' => 'approved',
            'created_by_id' => $this->ceo->id
        ]);

        $line = PurchaseOrderLine::query()->create([
            'company_id' => $this->company->id,
            'purchase_order_id' => $po->id,
            'product_id' => $this->product->id,
            'ordered_quantity' => 10,
            'unit_price' => 500,
            'line_total' => 5000,
        ]);

        $response = $this->actingAs($this->ceo, 'web')->postJson('/api/purchasing/goods-receipts', [
            'purchase_order_id' => $po->id,
            'warehouse_id' => $this->warehouse->id,
            'lines' => [
                ['purchase_order_line_id' => $line->id, 'quantity' => 10]
            ]
        ])->assertStatus(201);

        $this->assertDatabaseHas('journal_entries', [
            'company_id' => $this->company->id,
            'source_type' => 'goods_receipt',
            'source_id' => $response->json('id'),
            'currency' => 'IDR'
        ]);

        $entry = JournalEntry::where('source_type', 'goods_receipt')->where('source_id', $response->json('id'))->first();
        
        $this->assertCount(2, $entry->lines);
        $this->assertEquals(5000, $entry->lines->where('account.system_key', 'inventory')->first()->debit);
        $this->assertEquals(5000, $entry->lines->where('account.system_key', 'accounts_payable')->first()->credit);
    }

    public function test_pos_sale_creates_auto_journal()
    {
        $session = PosSession::query()->create([
            'company_id' => $this->company->id,
            'cashier_id' => $this->ceo->id,
            'opening_cash' => 1000,
            'status' => 'open',
            'opened_at' => now(),
        ]);

        $response = $this->actingAs($this->ceo, 'web')->postJson("/api/pos/sessions/{$session->id}/sales", [
            'total_amount' => 25000,
            'payment_method' => 'qris'
        ])->assertStatus(201);

        $this->assertDatabaseHas('journal_entries', [
            'company_id' => $this->company->id,
            'source_type' => 'pos_sale',
            'source_id' => $response->json('id')
        ]);

        $entry = JournalEntry::where('source_type', 'pos_sale')->where('source_id', $response->json('id'))->first();
        
        $this->assertCount(2, $entry->lines);
        $this->assertEquals(25000, $entry->lines->where('account.system_key', 'cash_bank')->first()->debit);
        $this->assertEquals(25000, $entry->lines->where('account.system_key', 'design_revenue')->first()->credit);
    }
}
