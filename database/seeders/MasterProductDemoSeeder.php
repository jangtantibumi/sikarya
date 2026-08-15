<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Company;
use App\Models\CompanyFeature;
use App\Models\CompanyMembership;
use App\Models\Goal;
use App\Models\Kpi;
use App\Models\KpiPlan;
use App\Models\Lead;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\PurchaseOrder;
use App\Services\GoodsReceiptService;
use App\Models\Task;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MasterProductDemoSeeder extends Seeder
{
    private const PASSWORD = 'NorthstarDemo!2026';

    public function run(): void
    {
        abort_unless(app()->environment(['local', 'testing']), 403, 'Demo data hanya boleh dibuat di local/testing.');

        $admin = $this->user(null, 'platform_admin', 'Platform Administrator', 'platform@master-erp.local', 'platform_admin', 'Platform Administrator');
        $studio = $this->company('studio-nusa', 'Studio Nusa', 'Professional Services');
        $kopi = $this->company('kopi-rasa', 'Kopi Rasa Nusantara', 'F&B');

        $studioPeople = [
            'ceo_studio' => ['Nadia Pratama', 'CEO', 'ceo'],
            'mgr_design' => ['Raka Mahendra', 'Design Director', 'mgr_design'],
            'staff_design' => ['Alya Putri', 'Interior Designer', 'staff_design'],
            'mgr_finance' => ['Sinta Laras', 'Finance Manager', 'mgr_finance'],
            'staff_hrd' => ['Dimas Putra', 'People Operations', 'staff_hrd'],
            'staff_marketing' => ['Bima Kurnia', 'Business Development', 'staff_marketing'],
        ];
        $kopiPeople = [
            'ceo_kopi' => ['Bagas Santoso', 'CEO', 'ceo'],
            'mgr_ops' => ['Reni Wulandari', 'Operations Manager', 'mgr_ops'],
            'staff_cashier' => ['Tara Safitri', 'Cashier Kemang', 'staff_ops'],
            'staff_warehouse' => ['Fajar Nugroho', 'Warehouse Officer', 'staff_ops'],
        ];
        $studioUsers = $this->people($studio, $studioPeople);
        $kopiUsers = $this->people($kopi, $kopiPeople);

        foreach ([$studio, $kopi] as $company) {
            foreach (array_keys(config('master_modules')) as $feature) {
                CompanyFeature::query()->updateOrCreate(['company_id' => $company->id, 'feature_key' => $feature], ['state' => 'active', 'updated_by_id' => $admin->id]);
            }
        }
        foreach (['inventory','purchasing','production','pos','project_costing'] as $feature) {
            CompanyFeature::query()->updateOrCreate(['company_id' => $kopi->id, 'feature_key' => $feature], ['state' => 'active', 'updated_by_id' => $admin->id]);
        }

        Lead::query()->updateOrCreate(['company_id' => $studio->id, 'client_name' => 'PT Langit Biru'], ['project_value' => 85000000, 'status' => 'penawaran', 'source' => 'Website', 'assigned_to' => $studioUsers['staff_marketing']->id, 'created_by' => $studioUsers['staff_marketing']->id]);
        $goal = Goal::query()->updateOrCreate(['company_id' => $studio->id, 'title' => 'Pertumbuhan proyek desain 2026'], ['description' => 'Meningkatkan proyek desain bernilai tinggi.', 'division' => 'marketing', 'year' => 2026, 'status' => 'approved', 'progress' => 42, 'created_by' => $studioUsers['ceo_studio']->id]);
        $plan = KpiPlan::query()->updateOrCreate(['company_id' => $studio->id, 'title' => 'KPI Marketing Q3'], ['goal_id' => $goal->id, 'division' => 'marketing', 'manager_id' => $studioUsers['staff_marketing']->id, 'status' => 'approved', 'score' => 42]);
        $kpi = Kpi::query()->updateOrCreate(['company_id' => $studio->id, 'title' => 'Proposal berkualitas'], ['kpi_plan_id' => $plan->id, 'target_value' => 12, 'unit' => 'proposal', 'weight' => 100, 'direction' => 'higher_is_better', 'aggregation_type' => 'count', 'data_source' => 'tasks', 'current_value' => 5]);
        Task::query()->updateOrCreate(['company_id' => $studio->id, 'title' => 'Susun proposal PT Langit Biru'], ['user_id' => $studioUsers['staff_marketing']->id, 'created_by_id' => $studioUsers['staff_marketing']->id, 'kpi_id' => $kpi->id, 'status' => 'in_progress', 'deadline' => now()->addDays(5), 'relation' => $kpi->title]);
        Attendance::query()->updateOrCreate(['company_id' => $studio->id, 'user_id' => $studioUsers['staff_design']->id, 'clock_in' => now()->startOfDay()->addHours(8)], ['clock_out' => null, 'status' => 'present', 'work_type' => 'office']);

        $warehouse = Warehouse::query()->updateOrCreate(['company_id' => $kopi->id, 'code' => 'PUSAT'], ['name' => 'Gudang Pusat', 'location' => 'Jakarta Selatan']);
        $beans = Product::query()->updateOrCreate(['company_id' => $kopi->id, 'sku' => 'BNS-ARAB-1KG'], ['name' => 'Biji Kopi Arabika 1kg', 'unit' => 'kg', 'reorder_level' => 15, 'standard_cost' => 185000]);
        Supplier::query()->updateOrCreate(['company_id' => $kopi->id, 'code' => 'SUP-BEAN'], ['name' => 'Nusantara Coffee Supply', 'email' => 'orders@coffee-supply.demo', 'phone' => '021-5550100', 'is_active' => true]);
        $supplier = Supplier::query()->where('company_id', $kopi->id)->where('code', 'SUP-BEAN')->firstOrFail();
        $po = PurchaseOrder::query()->updateOrCreate(['company_id' => $kopi->id, 'number' => 'PO-DEMO-0001'], ['supplier_id' => $supplier->id, 'status' => 'approved', 'order_date' => today(), 'expected_date' => today()->addDays(3), 'total_amount' => 3700000, 'created_by_id' => $kopiUsers['mgr_ops']->id, 'approved_by_id' => $kopiUsers['ceo_kopi']->id, 'approved_at' => now(), 'submitted_at' => now()]);
        $po->lines()->updateOrCreate(['product_id' => $beans->id], ['company_id' => $kopi->id, 'ordered_quantity' => 20, 'received_quantity' => 0, 'unit_price' => 185000, 'line_total' => 3700000]);
        $line = $po->lines()->where('product_id', $beans->id)->firstOrFail();
        if ($line->received_quantity == 0) {
            \App\Models\Account::query()->updateOrCreate(['company_id' => $kopi->id, 'code' => '1400', 'system_key' => 'inventory'], ['name' => 'Persediaan', 'type' => 'asset', 'normal_balance' => 'debit']);
            \App\Models\Account::query()->updateOrCreate(['company_id' => $kopi->id, 'code' => '2100', 'system_key' => 'accounts_payable'], ['name' => 'Hutang Usaha', 'type' => 'liability', 'normal_balance' => 'credit']);
            app(GoodsReceiptService::class)->receive($po, $warehouse, [['purchase_order_line_id' => $line->id, 'quantity' => 10]], $kopiUsers['mgr_ops']);
        }
        if (! StockMovement::query()->where('company_id', $kopi->id)->where('reference', 'OPENING-DEMO')->exists()) StockMovement::query()->create(['company_id' => $kopi->id, 'product_id' => $beans->id, 'warehouse_id' => $warehouse->id, 'type' => 'opening', 'quantity' => 48, 'unit_cost' => 185000, 'reference' => 'OPENING-DEMO', 'created_by_id' => $kopiUsers['mgr_ops']->id]);
    }

    private function company(string $slug, string $name, string $industry): Company { return Company::query()->updateOrCreate(['slug' => $slug], ['name' => $name, 'industry' => $industry, 'timezone' => 'Asia/Jakarta', 'currency' => 'IDR', 'status' => 'active']); }
    private function user(?Company $company, string $username, string $name, string $email, string $role, string $title): User { return User::query()->updateOrCreate(['username' => $username], ['name' => $name, 'email' => $email, 'role' => $role, 'job_title' => $title, 'company_id' => $company?->id, 'is_active' => true, 'account_status' => 'active', 'password' => Hash::make(self::PASSWORD)]); }
    private function people(Company $company, array $people): array { $users=[]; foreach ($people as $username => [$name,$title,$role]) { $user=$this->user($company,$username,$name, strtolower($username).'@'.$company->slug.'.local',$role,$title); CompanyMembership::query()->updateOrCreate(['company_id'=>$company->id,'user_id'=>$user->id],['role'=>$role,'is_owner'=>$role==='ceo','is_active'=>true]); $users[$username]=$user; } return $users; }
}
