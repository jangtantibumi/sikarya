<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$company = App\Models\Company::first();
$user = App\Models\User::find(2); // CEO
Auth::login($user);
$html = view('master-portal', [
    'company' => $company,
    'features' => app(App\Services\CompanyFeatureManager::class)->catalogue($company),
    'divisions' => App\Models\CompanyDivision::where('company_id', $company->id)->orderBy('order')->get(),
    'user' => $user,
    'roles' => App\Models\Role::with(['users' => function($q) use ($company) {
        $q->wherePivot('company_id', $company->id);
    }])->where('company_id', $company->id)->get(),
    'summary' => [],
    'journals' => [],
    'latestAnnouncement' => null,
    'myStaffs' => collect(),
    'pendingTasks' => 0,
    'pendingTasksStaff' => 0,
    'leaveQuotas' => collect(),
    'modules' => [
        ['id' => 'purchasing', 'name' => 'Purchasing', 'icon' => 'fa-cart-shopping', 'permanent' => true, 'dependencies' => [], 'state' => 'active'],
        ['id' => 'inventory', 'name' => 'Inventory', 'icon' => 'fa-box', 'permanent' => true, 'dependencies' => [], 'state' => 'active']
    ]
])->render();
file_put_contents('test_output.html', $html);
