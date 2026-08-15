<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$user = App\Models\User::first();
auth()->login($user);
$request = Illuminate\Http\Request::create('/master-demo/masterdatas', 'GET');
$response = $kernel->handle($request);
echo "STATUS: " . $response->getStatusCode() . "\n";
if (isset($response->exception) && $response->exception) {
    echo "EXCEPTION: " . $response->exception->getMessage() . "\n";
} else {
    echo "CONTENT: \n" . substr($response->getContent(), 0, 500);
}
