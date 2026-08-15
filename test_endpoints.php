<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = \App\Models\User::where('role', 'ceo')->first();
if (!$user) {
    $user = \App\Models\User::first();
}
auth()->login($user);

function testRoute($url) {
    try {
        $req = Illuminate\Http\Request::create($url, 'GET');
        $res = app()->handle($req);
        return $res->getStatusCode();
    } catch (\Exception $e) {
        return "ERROR: " . $e->getMessage();
    }
}

echo "Login: " . testRoute('/master-demo/login') . "\n";
echo "Dashboard CEO: " . testRoute('/master-demo/app') . "\n";
echo "CRM: " . testRoute('/api/crm/leads') . "\n";
echo "HR: " . testRoute('/api/attendance') . "\n";
echo "Inventory: " . testRoute('/api/inventory') . "\n";
echo "Documents: " . testRoute('/api/documents') . "\n";
echo "Task: " . testRoute('/api/tasks') . "\n";
echo "Notification: " . testRoute('/api/notifications') . "\n";
