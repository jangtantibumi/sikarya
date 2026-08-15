<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    // Mock user auth
    $user = \App\Models\User::first();
    if ($user) {
        \Illuminate\Support\Facades\Auth::login($user);
    }
    
    $request = \Illuminate\Http\Request::create('/crm/customers', 'GET');
    $controller = $app->make(\App\Http\Controllers\CrmController::class);
    $response = $controller->index($request);
    
    if ($response instanceof \Illuminate\View\View) {
        $response->render();
    }
    echo "SUCCESS\n";
} catch (\Exception $e) {
    echo "ERROR CLASS: " . get_class($e) . "\n";
    echo "ERROR MESSAGE: " . $e->getMessage() . "\n";
    echo "ERROR FILE: " . $e->getFile() . "\n";
    echo "ERROR LINE: " . $e->getLine() . "\n";
} catch (\Error $e) {
    echo "ERROR CLASS: " . get_class($e) . "\n";
    echo "ERROR MESSAGE: " . $e->getMessage() . "\n";
    echo "ERROR FILE: " . $e->getFile() . "\n";
    echo "ERROR LINE: " . $e->getLine() . "\n";
}
