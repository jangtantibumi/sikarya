<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Company;
use App\Models\Product;
use App\Models\Recipe;
use Illuminate\Support\Facades\Mail;

class RecipeAndRemediationTest extends TestCase
{
    use RefreshDatabase;

    public function test_ceo_can_create_recipe_successfully()
    {
        $company = Company::create(['name' => 'Suba Demo', 'slug' => 'suba-demo', 'email' => 'demo@suba.local']);
        $ceo = User::create([
            'company_id' => $company->id,
            'name' => 'CEO Suba',
            'username' => 'ceo_remediation',
            'email' => 'ceo_rem@suba.local',
            'password' => bcrypt('password'),
            'role' => 'ceo',
            'is_active' => true,
        ]);

        $finishedGood = Product::create([
            'company_id' => $company->id,
            'sku' => 'FG-001',
            'name' => 'Roti Sobek',
            'type' => 'finished_good',
            'price' => 15000,
            'stock' => 100,
            'unit' => 'pcs',
        ]);

        $rawMaterial = Product::create([
            'company_id' => $company->id,
            'sku' => 'RM-001',
            'name' => 'Tepung Terigu',
            'type' => 'raw_material',
            'price' => 12000,
            'stock' => 50000,
            'unit' => 'gram',
        ]);

        $response = $this->actingAs($ceo)->post(route('master-demo.recipes.store'), [
            'product_id' => $finishedGood->id,
            'name' => 'Resep Roti Sobek Super',
            'yield_quantity' => 50,
            'materials' => [$rawMaterial->id],
            'quantities' => [500],
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('recipes', [
            'company_id' => $company->id,
            'product_id' => $finishedGood->id,
            'name' => 'Resep Roti Sobek Super',
        ]);
    }

    public function test_non_ceo_cannot_create_recipe()
    {
        $company = Company::create(['name' => 'Suba Demo 2', 'slug' => 'suba-demo-2', 'email' => 'demo2@suba.local']);
        $staff = User::create([
            'company_id' => $company->id,
            'name' => 'Staff Ops',
            'username' => 'staff_remediation',
            'email' => 'staff_rem@suba.local',
            'password' => bcrypt('password'),
            'role' => 'staff_ops',
            'is_active' => true,
        ]);

        $response = $this->actingAs($staff)->post(route('master-demo.recipes.store'), [
            'product_id' => 1,
            'name' => 'Unauthorized Recipe',
            'yield_quantity' => 10,
            'materials' => [1],
            'quantities' => [100],
        ]);

        $response->assertStatus(403);
    }

    public function test_login_triggers_mail_notification()
    {
        Mail::fake();

        $company = Company::create(['name' => 'Suba Demo 3', 'slug' => 'suba-demo-3', 'email' => 'demo3@suba.local']);
        $ceo = User::create([
            'company_id' => $company->id,
            'name' => 'CEO Login Test',
            'username' => 'ceo_demo_test',
            'password' => bcrypt('password123'),
            'role' => 'ceo',
            'is_active' => true,
            'email' => 'ceo@suba-erp.local',
        ]);

        $response = $this->post(route('master-demo.login.attempt'), [
            'username' => 'ceo_demo_test',
            'password' => 'password123',
        ]);

        $response->assertStatus(302);
        Mail::assertSent(\App\Mail\CeoLoginNotificationMail::class, function ($mail) {
            return $mail->hasTo('ceo@suba-erp.local');
        });
    }
}
