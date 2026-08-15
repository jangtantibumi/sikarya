<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Http\Request;

// Authenticate user
$user = User::first() ?? User::create([
    'name' => 'Admin User',
    'username' => 'admin_demo',
    'email' => 'admin@suba-erp.com',
    'password' => bcrypt('password'),
]);
Auth::login($user);

$inventoryRoutes = [
    '/inventory/dashboard',
    '/inventory/items',
    '/inventory/items/create',
    '/inventory/items/1',
    '/inventory/items/1/edit',
    '/inventory/categories',
    '/inventory/brands',
    '/inventory/uoms',
    '/inventory/warehouses',
    '/inventory/warehouses/create',
    '/inventory/warehouses/1',
    '/inventory/locations',
    '/inventory/stock-summary',
    '/inventory/stock-in',
    '/inventory/stock-in/create',
    '/inventory/stock-in/1',
    '/inventory/stock-out',
    '/inventory/stock-out/create',
    '/inventory/stock-out/1',
    '/inventory/transfers',
    '/inventory/transfers/create',
    '/inventory/transfers/1',
    '/inventory/adjustments',
    '/inventory/adjustments/create',
    '/inventory/adjustments/1',
    '/inventory/cycle-counts',
    '/inventory/cycle-counts/create',
    '/inventory/cycle-counts/1',
    '/inventory/reservations',
    '/inventory/reservations/create',
    '/inventory/reservations/1',
    '/inventory/pickings',
    '/inventory/pickings/create',
    '/inventory/pickings/1',
    '/inventory/packings',
    '/inventory/packings/create',
    '/inventory/packings/1',
    '/inventory/deliveries',
    '/inventory/deliveries/create',
    '/inventory/deliveries/1',
    '/inventory/stock-ledger',
    '/inventory/serial-numbers',
    '/inventory/batch-numbers',
    '/inventory/barcodes',
    '/inventory/reports',
    '/inventory/analytics',
    '/inventory/settings',
];

echo "=== AUDIT INVENTORY ROUTES HTTP STATUS ===\n\n";

$allPassed = true;
$results = [];

foreach ($inventoryRoutes as $uri) {
    Auth::login($user);
    $request = Request::create($uri, 'GET');
    $request->setUserResolver(function() use ($user) {
        return $user;
    });

    $response = $app->handle($request);
    $status = $response->getStatusCode();
    $results[$uri] = $status;

    if ($status === 200) {
        echo "[OK] 200 - $uri\n";
    } else {
        echo "[FAIL] $status - $uri\n";
        $allPassed = false;
    }
}

echo "\n==========================================\n";
if ($allPassed) {
    echo "SUCCESS: ALL " . count($inventoryRoutes) . " INVENTORY ROUTES RETURNED HTTP 200!\n";
} else {
    echo "FAILED: SOME ROUTES RETURNED ERROR CODES!\n";
}
