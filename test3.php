<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::create('/master-portal', 'GET');
$request->setUserResolver(function() { return App\Models\User::find(2); });
$response = $kernel->handle($request);
file_put_contents('test_output.html', $response->getContent());
