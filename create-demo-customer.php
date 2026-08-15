<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$customer = \App\Models\CrmCustomer::updateOrCreate(
    ['customer_code' => 'DEMO-001'], 
    [
        'name' => 'Demo Customer', 
        'phone' => '08123456789', 
        'email' => 'demo@example.com', 
        'is_active' => true, 
        'membership_level' => 'Gold', 
        'points' => 1500, 
        'total_spent' => 250000
    ]
); 
echo 'Demo Customer Created: ' . $customer->customer_code . ' | Phone: ' . $customer->phone;
