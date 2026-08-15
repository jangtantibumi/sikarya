<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\CompanyMembership;
use App\Models\Lead;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\CompanyFeatureManager;
use App\Services\InventoryLedgerService;
use App\Services\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterTenantDemoTest extends TestCase
{
    use RefreshDatabase;

    public function test_feature_dependencies_are_enforced_per_company(): void
    {
        $company = Company::query()->create(['name' => 'Tenant A', 'slug' => 'tenant-a']);
        $features = app(CompanyFeatureManager::class);

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $features->set($company, 'production', 'active');
    }

    public function test_company_can_activate_operations_in_dependency_order(): void
    {
        $company = Company::query()->create(['name' => 'Tenant Operations', 'slug' => 'tenant-operations']);
        $features = app(CompanyFeatureManager::class);

        $features->set($company, 'inventory', 'active');
        $features->set($company, 'purchasing', 'active');
        $production = $features->set($company, 'production', 'active');

        $this->assertSame('active', $production->state);
    }

    public function test_company_records_are_queryable_in_their_own_context(): void
    {
        $user = User::factory()->create();
        $first = Company::query()->create(['name' => 'Tenant A', 'slug' => 'tenant-a']);
        $second = Company::query()->create(['name' => 'Tenant B', 'slug' => 'tenant-b']);
        Lead::query()->create(['company_id' => $first->id, 'client_name' => 'A Client', 'assigned_to' => $user->id]);
        Lead::query()->create(['company_id' => $second->id, 'client_name' => 'B Client', 'assigned_to' => $user->id]);

        $this->assertSame(['A Client'], $first->leads()->pluck('client_name')->all());
        $this->assertSame(['B Client'], $second->leads()->pluck('client_name')->all());
    }

    public function test_active_tenant_cannot_read_or_write_another_tenant_lead(): void
    {
        $user = User::factory()->create();
        $first = Company::query()->create(['name' => 'Tenant A', 'slug' => 'tenant-a']);
        $second = Company::query()->create(['name' => 'Tenant B', 'slug' => 'tenant-b']);
        Lead::query()->create(['company_id' => $first->id, 'client_name' => 'A Client', 'assigned_to' => $user->id]);
        Lead::query()->create(['company_id' => $second->id, 'client_name' => 'B Client', 'assigned_to' => $user->id]);

        $context = app(TenantContext::class);
        $context->setCompany($first);

        try {
            $this->assertSame(['A Client'], Lead::query()->pluck('client_name')->all());

            $created = Lead::query()->create(['client_name' => 'Scoped Client', 'assigned_to' => $user->id]);
            $this->assertSame($first->id, $created->company_id);
            $this->assertNull(Lead::query()->where('client_name', 'B Client')->first());
        } finally {
            $context->clear();
        }
    }

    public function test_only_tenant_owner_or_platform_admin_can_manage_company_modules(): void
    {
        $company = Company::query()->create(['name' => 'Tenant A', 'slug' => 'tenant-a']);
        $owner = User::factory()->create(['role' => 'ceo', 'company_id' => $company->id]);
        $manager = User::factory()->create(['role' => 'mgr_marketing', 'company_id' => $company->id]);
        $platformAdmin = User::factory()->create(['role' => 'platform_admin']);
        CompanyMembership::query()->create([
            'company_id' => $company->id,
            'user_id' => $manager->id,
            'role' => 'manager',
            'is_active' => true,
        ]);

        $this->assertTrue($owner->can('manageModules', $company));
        $this->assertFalse($manager->can('manageModules', $company));
        $this->assertTrue($platformAdmin->can('manageModules', $company));
    }

    public function test_tenant_ceo_can_view_and_switch_only_its_company_modules(): void
    {
        $company = Company::query()->create(['name' => 'Tenant A', 'slug' => 'tenant-a']);
        $ceo = User::factory()->create([
            'role' => 'ceo',
            'company_id' => $company->id,
            'is_active' => true,
            'account_status' => 'active',
        ]);

        $response = $this->actingAs($ceo, 'web')
            ->getJson('/api/company/module-control');

        $response->assertOk()
            ->assertJsonPath('company.id', $company->id)
            ->assertJsonPath('can_manage_modules', true);

        $this->actingAs($ceo, 'web')
            ->putJson('/api/company/features/inventory', ['state' => 'active'])
            ->assertOk()
            ->assertJsonPath('feature.state', 'active');

        $this->actingAs($ceo, 'web')
            ->putJson('/api/company/features/production', ['state' => 'active'])
            ->assertStatus(422);
    }

    public function test_tenant_feature_state_is_enforced_by_existing_module_routes(): void
    {
        $company = Company::query()->create(['name' => 'Tenant CRM', 'slug' => 'tenant-crm']);
        $ceo = User::factory()->create([
            'role' => 'ceo',
            'company_id' => $company->id,
            'is_active' => true,
            'account_status' => 'active',
        ]);

        app(CompanyFeatureManager::class)->set($company, 'crm', 'off');

        $this->actingAs($ceo, 'web')
            ->getJson('/api/crm/leads')
            ->assertForbidden()
            ->assertJsonPath('code', 'TENANT_FEATURE_DISABLED');

        app(CompanyFeatureManager::class)->set($company, 'crm', 'read_only');

        $this->actingAs($ceo, 'web')
            ->postJson('/api/crm/leads', [])
            ->assertForbidden()
            ->assertJsonPath('code', 'TENANT_FEATURE_READ_ONLY');
    }

    public function test_local_demo_displays_companies_and_module_control(): void
    {
        $this->seed(\Database\Seeders\MasterProductDemoSeeder::class);

        $this->actingAs(User::query()->where('username', 'platform_admin')->firstOrFail(), 'web')
            ->get('/master-demo/app')
            ->assertOk()
            ->assertSee('Studio Nusa')
            ->assertSee('Module Controls');
    }

    public function test_inventory_ledger_prevents_negative_stock(): void
    {
        $company = Company::query()->create(['name' => 'Goods Co', 'slug' => 'goods-co']);
        $product = Product::query()->create(['company_id' => $company->id, 'sku' => 'SKU-1', 'name' => 'Produk Uji']);
        $warehouse = Warehouse::query()->create(['company_id' => $company->id, 'code' => 'UTAMA', 'name' => 'Gudang Utama']);
        $ledger = app(InventoryLedgerService::class);
        $ledger->move($product, $warehouse, 10, 'receipt');
        $ledger->move($product, $warehouse, -4, 'issue');
        $this->assertSame(6.0, $ledger->balance($product, $warehouse));
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $ledger->move($product, $warehouse, -7, 'issue');
    }
}
