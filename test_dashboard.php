<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

// Find a user or create a session
$user = \App\Models\User::first();
if ($user) {
    \Illuminate\Support\Facades\Auth::login($user);
}

// Clear log file first
file_put_contents(__DIR__.'/storage/logs/laravel.log', '');

$request = Illuminate\Http\Request::create('/inventory/dashboard', 'GET');
$response = $kernel->handle($request);

echo "HTTP Status Code: " . $response->getStatusCode() . "\n";

$logContent = file_get_contents(__DIR__.'/storage/logs/laravel.log');
if (!empty($logContent)) {
    echo "--- LARAVEL.LOG CONTENT ---\n";
    echo $logContent;
    echo "---------------------------\n";
} else {
    echo "laravel.log is empty.\n";
}

if ($response->getStatusCode() === 500 && isset($response->exception)) {
    $e = $response->exception;
    echo "Exception Class: " . get_class($e) . "\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
