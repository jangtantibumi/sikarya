<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\CompanyFeature;
use App\Models\CompanyMembership;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class KikoBakesSeeder extends Seeder
{
    private const PASSWORD = 'password123';

    public function run(): void
    {
        $company = $this->createCompany('kiko-bakes', 'Kiko Bakes', 'F&B / Bakery');

        // Enable modules
        $admin = User::where('role', 'platform_admin')->first() ?? User::first();
        foreach (array_keys(config('master_modules', ['inventory'=>true,'purchasing'=>true,'production'=>true,'pos'=>true])) as $feature) {
            CompanyFeature::query()->updateOrCreate(
                ['company_id' => $company->id, 'feature_key' => $feature],
                ['state' => 'active', 'updated_by_id' => $admin?->id]
            );
        }

        // --- HIERARCHY MAPPING ---

        // 1. TOP LEVEL
        $owner = $this->createUser($company, 'jeslyn_lysandra', 'Jeslyn Lysandra', 'jeslyn@kikobakes.local', 'ceo', 'Owner', null);
        $ceo = $this->createUser($company, 'robil_alaminto', 'Robil Alaminto', 'robil@kikobakes.local', 'ceo', 'CEO', 'jeslyn_lysandra');

        // 2. MARKETING DIVISION (Mostly Dummies, plus Gebil)
        $marketingMgr = $this->createUser($company, 'marketing_strategist', 'Dummy Marketing Strategist', 'marketing@kikobakes.local', 'mgr_marketing', 'Marketing Strategist', 'robil_alaminto');
        $sosmedSpec = $this->createUser($company, 'gebil_sosmed', 'Gebil', 'gebillakhoirunnisa@gmail.com', 'staff_marketing', 'Social Media Specialist', 'marketing_strategist');
        $sosmedAdmin = $this->createUser($company, 'sosmed_admin', 'Dummy Social Media Admin', 'sosmed.admin@kikobakes.local', 'staff_marketing', 'Social Media Admin', 'gebil_sosmed');
        
        $executor = $this->createUser($company, 'executor', 'Dummy Executor', 'executor@kikobakes.local', 'staff_ops', 'Executor', 'marketing_strategist');
        $delivery = $this->createUser($company, 'delivery_officer', 'Dummy Delivery Officer', 'delivery@kikobakes.local', 'staff_ops', 'Delivery Officer', 'executor');

        // 3. RETAIL OPERATION DIVISION
        $retailMgr = $this->createUser($company, 'retail_manager', 'Dummy Retail Manager', 'retail@kikobakes.local', 'mgr_ops', 'Retail Operation Manager', 'robil_alaminto');
        
        // Sukabumi Branch
        $spvSukabumi = $this->createUser($company, 'della_sukabumi', 'Della', 'ramkiranadella@gmail.com', 'mgr_ops', 'Supervisor Operational Branch (Sukabumi)', 'retail_manager', 'Sukabumi');
        $this->createUser($company, 'tri_kasir', 'Tri', 'triagustinaputri08@gmail.com', 'staff_ops', 'Staff Kasir (Sukabumi)', 'della_sukabumi', 'Sukabumi');
        
        // Cisaat Branch
        $spvCisaat = $this->createUser($company, 'terra_cisaat', 'Terra', 'terranovia6@gmail.com', 'mgr_ops', 'Supervisor Operational Branch (Cisaat)', 'retail_manager', 'Cisaat');
        $this->createUser($company, 'sahla_kasir', 'Sahla', 'sahlanurmaulida1@gmail.com', 'staff_ops', 'Staff Kasir (Cisaat)', 'terra_cisaat', 'Cisaat');
        
        // Additional Cashiers/Admins (Assume reporting directly to Retail Manager if no branch specified)
        $this->createUser($company, 'difa_admin', 'Difa', 'difalaisaamanda1616@gmail.com', 'staff_ops', 'Admin Toko', 'retail_manager');
        $this->createUser($company, 'reyna_kasir', 'Reyna', 'reynanazwa374@gmail.com', 'staff_ops', 'Staff Kasir', 'retail_manager');

        // 4. PRODUCTION DIVISION
        $prodMgr = $this->createUser($company, 'astri_prod', 'Astri', 'astrifatimah60@gmail.com', 'mgr_production', 'Production Manager', 'robil_alaminto');
        
        // Pastry Sub-division
        $pastryHead = $this->createUser($company, 'viona_pastry', 'Viona', 'vionaksma@gmail.com', 'mgr_production', 'Pastry Head of Production', 'astri_prod');
        $this->createUser($company, 'ardani_pastry', 'Ardani', 'ardaniqq@gmail.com', 'staff_production', 'Staff Produksi Pastry', 'viona_pastry');
        $this->createUser($company, 'najiah_pastry', 'Najiah', 'najiahsahla3@gmail.com', 'staff_production', 'Staff Produksi Pastry', 'viona_pastry');

        // Breadline Sub-division
        $breadHead = $this->createUser($company, 'debi_bread', 'Debi', 'debiputrimr17@gmail.com', 'mgr_production', 'Breadline Head of Production', 'astri_prod');
        $this->createUser($company, 'ana_bread', 'Ana', 'ananuraeni2306@gmail.com', 'staff_production', 'Staff Produksi Bakery', 'debi_bread');
        $this->createUser($company, 'randi_bread', 'Randi', 'randimuhamad353@gmail.com', 'staff_production', 'Staff Produksi Bakery', 'debi_bread');
        $this->createUser($company, 'dika_bread', 'Dika', 'andikapermana0691@gmail.com', 'staff_production', 'Staff Produksi Bakery', 'debi_bread');
        $this->createUser($company, 'akbar_bread', 'Akbar', 'akbarsaragih605@gmail.com', 'staff_production', 'Staff Produksi Bakery', 'debi_bread');

        // 5. PURCHASING DIVISION
        $this->createUser($company, 'rima_purchasing', 'Rima', 'ngrimaaa@gmail.com', 'mgr_purchasing', 'Purchasing', 'robil_alaminto');
    }

    private function createCompany(string $slug, string $name, string $industry): Company
    {
        return Company::query()->updateOrCreate(
            ['slug' => $slug],
            [
                'name' => $name,
                'industry' => $industry,
                'timezone' => 'Asia/Jakarta',
                'currency' => 'IDR',
                'status' => 'active'
            ]
        );
    }

    private function createUser(Company $company, string $username, string $name, string $email, string $role, string $title, ?string $parentUsername, ?string $branch = null): User
    {
        $user = User::query()->updateOrCreate(
            ['username' => $username],
            [
                'name' => $name,
                'email' => $email,
                'role' => $role,
                'job_title' => $title,
                'company_id' => $company->id,
                'parent' => $parentUsername,
                'branch_location' => $branch,
                'is_active' => true,
                'account_status' => 'active',
                'password' => Hash::make(self::PASSWORD)
            ]
        );

        CompanyMembership::query()->updateOrCreate(
            ['company_id' => $company->id, 'user_id' => $user->id],
            [
                'role' => $role,
                'is_owner' => ($role === 'ceo' && $parentUsername === null),
                'is_active' => true
            ]
        );

        return $user;
    }
}
