<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('role', 'ceo')->first();
auth()->login($user);

$request = Illuminate\Http\Request::create('/organization/tree', 'GET');
$response = app()->handle($request);
echo "STATUS: " . $response->getStatusCode() . "\n";
echo $response->getContent();
