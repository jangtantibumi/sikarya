<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\CrmBroadcastLog;
use App\Models\CrmCampaign;
use App\Models\CrmCoupon;
use App\Models\CrmPromotion;
use App\Models\CrmReferral;
use App\Models\CrmSegment;
use App\Models\CrmTag;
use App\Models\CrmVoucher;
use App\Services\CrmMarketingService;
use Illuminate\Http\Request;

class CrmMarketingController extends Controller
{
    protected $marketingService;

    public function __construct(CrmMarketingService $marketingService)
    {
        $this->marketingService = $marketingService;
    }

    // Main Marketing Hub
    public function index()
    {
        $campaignsCount = CrmCampaign::count();
        $broadcastsCount = CrmBroadcastLog::count();
        $promotionsCount = CrmPromotion::where('is_active', true)->count();
        $referralsCount = CrmReferral::count();

        $recentCampaigns = CrmCampaign::orderBy('id', 'desc')->take(5)->get();
        $upcomingBirthdays = $this->marketingService->getUpcomingBirthdays();

        return view('crm.marketing.index', compact(
            'campaignsCount',
            'broadcastsCount',
            'promotionsCount',
            'referralsCount',
            'recentCampaigns',
            'upcomingBirthdays'
        ));
    }

    // =====================================
    // CAMPAIGN & BROADCAST
    // =====================================
    public function campaigns()
    {
        $campaigns = CrmCampaign::withCount('broadcastLogs')->orderBy('id', 'desc')->paginate(15);
        $tags = CrmTag::all();
        $segments = CrmSegment::all();

        return view('crm.marketing.campaigns', compact('campaigns', 'tags', 'segments'));
    }

    public function storeCampaign(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'channel' => 'required|in:whatsapp,email,broadcast',
            'target_type' => 'required|in:all,segment,tag',
            'target_id' => 'nullable|integer',
            'subject' => 'nullable|string|max:255',
            'message_body' => 'required|string',
            'scheduled_at' => 'nullable|date',
            'send_now' => 'nullable|boolean',
        ]);

        $campaign = $this->marketingService->createCampaign($validated);

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Campaign berhasil dibuat.', 'data' => $campaign]);
        }

        return redirect()->route('crm.marketing.campaigns')->with('success', 'Campaign berhasil dibuat.');
    }

    public function sendCampaign($id)
    {
        $count = $this->marketingService->executeCampaign($id);

        return back()->with('success', "Broadcast campaign berhasil dikirimkan ke {$count} customer.");
    }

    public function broadcastLogs()
    {
        $logs = CrmBroadcastLog::with(['campaign', 'customer'])->orderBy('id', 'desc')->paginate(20);

        return view('crm.marketing.broadcast_logs', compact('logs'));
    }

    // =====================================
    // BIRTHDAY REMINDER
    // =====================================
    public function birthdays()
    {
        $upcomingBirthdays = $this->marketingService->getUpcomingBirthdays();

        return view('crm.marketing.birthdays', compact('upcomingBirthdays'));
    }

    public function sendBirthdayReward(Request $request, $customerId)
    {
        $customer = $this->marketingService->sendBirthdayRewards($customerId);

        return back()->with('success', "Bonus ulang tahun berhasil dikirimkan ke {$customer->name}.");
    }

    // =====================================
    // PROMOTION ENGINE
    // =====================================
    public function promotions()
    {
        $promotions = CrmPromotion::orderBy('id', 'desc')->paginate(15);

        return view('crm.marketing.promotions', compact('promotions'));
    }

    public function storePromotion(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'promo_code' => 'required|string|max:50|unique:crm_promotions',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'min_spend' => 'required|numeric|min:0',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active') ? true : false;
        $validated['promo_code'] = strtoupper($validated['promo_code']);

        CrmPromotion::create($validated);

        return redirect()->route('crm.marketing.promotions')->with('success', 'Promo berhasil dibuat.');
    }

    public function checkPromotion(Request $request)
    {
        $validated = $request->validate([
            'promo_code' => 'required|string',
            'cart_total' => 'required|numeric|min:0',
        ]);

        $result = $this->marketingService->applyPromotion($validated['promo_code'], $validated['cart_total']);

        return response()->json($result);
    }

    // =====================================
    // COUPON ENGINE
    // =====================================
    public function coupons()
    {
        $coupons = CrmCoupon::with(['voucher', 'customer'])->orderBy('id', 'desc')->paginate(15);
        $vouchers = CrmVoucher::where('is_active', true)->get();

        return view('crm.marketing.coupons', compact('coupons', 'vouchers'));
    }

    public function generateCoupon(Request $request)
    {
        $validated = $request->validate([
            'voucher_id' => 'required|exists:crm_vouchers,id',
            'customer_id' => 'nullable|exists:crm_customers,id',
        ]);

        $coupon = $this->marketingService->generateCoupon($validated['voucher_id'], $validated['customer_id'] ?? null);

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'data' => $coupon]);
        }

        return back()->with('success', "Kupon {$coupon->coupon_code} berhasil dibuat!");
    }

    public function validateCoupon(Request $request)
    {
        $validated = $request->validate([
            'coupon_code' => 'required|string',
        ]);

        $result = $this->marketingService->validateCoupon($validated['coupon_code']);

        return response()->json($result);
    }

    // =====================================
    // REFERRAL PROGRAM
    // =====================================
    public function referrals()
    {
        $referrals = CrmReferral::with(['referrer', 'referee'])->orderBy('id', 'desc')->paginate(15);

        return view('crm.marketing.referrals', compact('referrals'));
    }
}
