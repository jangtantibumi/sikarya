<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$user = \App\Models\User::first();
if ($user) {
    \Illuminate\Support\Facades\Auth::login($user);
}

$urls = [
    '/crm/dashboard',
    '/crm/customers',
    '/crm/memberships',
    '/crm/loyalties',
    '/crm/vouchers',
    '/crm/reservations',
    '/crm/feedbacks',
    '/crm/analytics',
];

echo "Testing CRM URLs...\n";
foreach ($urls as $url) {
    $request = \Illuminate\Http\Request::create($url, 'GET');
    $response = $kernel->handle($request);
    echo str_pad($url, 25) . " => HTTP " . $response->getStatusCode() . "\n";
    $kernel->terminate($request, $response);
}
echo "Done.\n";
