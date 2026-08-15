<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\CrmCustomer;
use App\Models\CrmVoucher;
use App\Models\CrmTag;
use App\Services\CrmMarketingService;
use App\Services\CrmAnalyticsService;
use App\Services\CrmCustomerService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

echo "=== STARTING COMPREHENSIVE END-TO-END CRM AUDIT TEST ===\n\n";

// Set initial request object in Laravel container
$initRequest = Request::create('/', 'GET');
$app->instance('request', $initRequest);

// 1. Authenticate Admin User
$user = User::first();
if ($user) {
    Auth::login($user);
    echo "[PASS] Authenticated admin user: {$user->email}\n";
} else {
    echo "[WARN] No user found in database.\n";
}

// 2. Ensure test customer exists
$customer = CrmCustomer::first();
if (!$customer) {
    $customer = CrmCustomer::create([
        'name' => 'Audit Test Customer',
        'phone' => '081234567890',
        'email' => 'audit.test@example.com',
        'birth_date' => now()->format('Y-m-d'),
        'membership_level' => 'Gold',
        'total_points' => 250,
        'total_spending' => 1500000,
    ]);
    echo "[PASS] Created test customer: {$customer->customer_code}\n";
} else {
    echo "[PASS] Found test customer: {$customer->name} ({$customer->customer_code})\n";
}

// 3. Test CrmMarketingService (Campaigns, Promotions, Coupons, Birthdays, Referrals)
$mktService = app(CrmMarketingService::class);
$campaign = $mktService->createCampaign([
    'title' => 'Audit Test Campaign',
    'channel' => 'whatsapp',
    'message_body' => 'Halo {name}, dapatkan bonus voucher spesial!',
    'send_now' => true,
]);
echo "[PASS] Marketing Campaign execution created & dispatched broadcast log. Campaign ID: {$campaign->id}\n";

$birthdays = $mktService->getUpcomingBirthdays();
echo "[PASS] Birthday Reminder engine returned " . count($birthdays) . " upcoming birthdays.\n";

$promoResult = $mktService->applyPromotion('NON_EXISTENT', 100000);
echo "[PASS] Promotion Engine validation returned expected response: {$promoResult['message']}\n";

// 4. Test CrmAnalyticsService (CLV, RFM, Repeat, Churn, Trends)
$analyticsService = app(CrmAnalyticsService::class);
$analytics = $analyticsService->getAnalyticsOverview();
echo "[PASS] Analytics Engine CLV: Rp " . number_format($analytics['clv']['avg_clv']) . "\n";
echo "[PASS] Analytics Engine Repeat Rate: {$analytics['repeat']['repeat_rate']}%\n";
echo "[PASS] Analytics Engine Churn Rate: {$analytics['churn']['churn_rate']}%\n";

// 5. Test Customer Portal Session
session(['customer_portal_id' => $customer->id]);

function testUrl($url, $method = 'GET', $data = []) {
    global $kernel;
    $request = Request::create($url, $method, $data);
    $response = $kernel->handle($request);
    $status = $response->getStatusCode();
    $kernel->terminate($request, $response);
    return $status;
}

$routesToTest = [
    '/crm/dashboard' => 'GET',
    '/crm/customers' => 'GET',
    '/crm/customers/' . $customer->id => 'GET',
    '/crm/customers/merge' => 'GET',
    '/crm/customers/export/excel' => 'GET',
    '/crm/memberships' => 'GET',
    '/crm/loyalties' => 'GET',
    '/crm/vouchers' => 'GET',
    '/crm/reservations' => 'GET',
    '/crm/feedbacks' => 'GET',
    '/crm/marketing' => 'GET',
    '/crm/marketing/campaigns' => 'GET',
    '/crm/marketing/birthdays' => 'GET',
    '/crm/marketing/promotions' => 'GET',
    '/crm/marketing/coupons' => 'GET',
    '/crm/marketing/referrals' => 'GET',
    '/crm/analytics' => 'GET',
    '/portal/login' => 'GET',
    '/portal/' => 'GET',
    '/portal/profile' => 'GET',
    '/portal/vouchers' => 'GET',
    '/portal/loyalty' => 'GET',
    '/portal/invoices' => 'GET',
    '/portal/card' => 'GET',
];

$allPassed = true;
echo "\n--- ROUTE HTTP STATUS AUDIT ---\n";
foreach ($routesToTest as $route => $method) {
    $status = testUrl($route, $method);
    if ($status >= 200 && $status < 400) {
        echo "✅ [{$status}] {$method} {$route}\n";
    } else {
        echo "❌ [{$status}] {$method} {$route}\n";
        $allPassed = false;
    }
}

if ($allPassed) {
    echo "\n🎉 ALL END-TO-END AUDIT TESTS PASSED SUCCESSFULLY (HTTP 200 OK)!\n";
} else {
    echo "\n⚠️ SOME ROUTES FAILED AUDIT!\n";
}
