<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::first();
if ($user) {
    \Illuminate\Support\Facades\Auth::login($user);
}

$results = [];

// Helper function to test a URL
function testRoute($url, $method = 'GET', $data = []) {
    global $kernel;
    $request = \Illuminate\Http\Request::create($url, $method, $data);
    $response = $kernel->handle($request);
    $status = $response->getStatusCode();
    $kernel->terminate($request, $response);
    return $status;
}

// 1. Dashboard, Customers, Memberships, Loyalties, Vouchers, Analytics
$routes = [
    '/crm/dashboard',
    '/crm/customers',
    '/crm/memberships',
    '/crm/loyalties',
    '/crm/vouchers',
    '/crm/reservations',
    '/crm/feedbacks',
    '/crm/analytics',
    '/portal/login',
];

foreach ($routes as $route) {
    $status = testRoute($route);
    $results[$route] = $status;
}

echo json_encode($results, JSON_PRETTY_PRINT);
