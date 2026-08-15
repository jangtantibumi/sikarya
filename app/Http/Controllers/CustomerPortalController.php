<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\CrmCustomer;
use App\Models\CrmCustomerPointHistory;
use App\Models\CrmCustomerTimeline;
use App\Models\CrmCustomerVoucher;
use App\Models\CrmFeedback;
use App\Models\CrmReservation;
use App\Models\CrmVoucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CustomerPortalController extends Controller
{
    public function loginForm()
    {
        return view('portal.login');
    }

    public function loginAttempt(Request $request)
    {
        $request->validate([
            'customer_code' => 'required|string',
            'phone' => 'required|string',
        ]);

        $customer = CrmCustomer::where('customer_code', $request->customer_code)
            ->where('phone', $request->phone)
            ->first();

        if ($customer) {
            if ($customer->is_blacklisted) {
                return back()->withErrors(['error' => 'Akun Anda sedang dibatasi (Blacklisted). Silakan hubungi customer service.']);
            }

            Session::put('customer_portal_id', $customer->id);

            return redirect()->route('portal.dashboard');
        }

        return back()->withErrors(['error' => 'Kode Customer atau Nomor HP salah.']);
    }

    public function logout()
    {
        Session::forget('customer_portal_id');

        return redirect()->route('portal.login');
    }

    public function dashboard()
    {
        $customerId = Session::get('customer_portal_id');
        $customer = CrmCustomer::with(['pointHistories' => function ($q) {
            $q->orderBy('created_at', 'desc')->take(5);
        }, 'reservations' => function ($q) {
            $q->orderBy('reservation_date', 'desc')->take(5);
        }])->findOrFail($customerId);

        return view('portal.dashboard', compact('customer'));
    }

    public function submitReservation(Request $request)
    {
        $customerId = Session::get('customer_portal_id');
        $customer = CrmCustomer::findOrFail($customerId);

        if ($customer->is_blacklisted) {
            return back()->withErrors(['error' => 'Akun Anda diblacklist dari membuat reservasi.']);
        }

        $validated = $request->validate([
            'reservation_date' => 'required|date|after_or_equal:today',
            'reservation_time' => 'required',
            'pax' => 'required|integer|min:1',
            'special_requests' => 'nullable|string|max:500',
        ]);

        $validated['customer_id'] = $customerId;
        $validated['status'] = 'Pending';

        $reservation = CrmReservation::create($validated);

        CrmCustomerTimeline::create([
            'customer_id' => $customerId,
            'action' => 'RESERVATION_CREATED',
            'description' => "Reservasi (melalui Portal) dibuat untuk tanggal {$reservation->reservation_date->format('d/m/Y')} {$reservation->reservation_time} ({$reservation->pax} Pax).",
            'reference_id' => $reservation->id,
        ]);

        return back()->with('success', 'Reservasi berhasil dikirim. Menunggu konfirmasi admin.');
    }

    public function submitFeedback(Request $request)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'category' => 'required|in:Food,Service,Ambience,Other',
            'message' => 'required|string|max:1000',
        ]);

        $customerId = Session::get('customer_portal_id');
        $validated['customer_id'] = $customerId;
        $validated['status'] = 'Open';

        $feedback = CrmFeedback::create($validated);

        CrmCustomerTimeline::create([
            'customer_id' => $customerId,
            'action' => 'FEEDBACK_CREATED',
            'description' => "Feedback (melalui Portal) dikirimkan. Kategori: {$feedback->category}. Rating: {$feedback->rating}/5.",
            'reference_id' => $feedback->id,
        ]);

        return back()->with('success', 'Terima kasih atas feedback Anda!');
    }

    // =====================================
    // EDIT PROFILE
    // =====================================
    public function profile()
    {
        $customerId = Session::get('customer_portal_id');
        $customer = CrmCustomer::findOrFail($customerId);

        return view('portal.profile', compact('customer'));
    }

    public function updateProfile(Request $request)
    {
        $customerId = Session::get('customer_portal_id');
        $customer = CrmCustomer::findOrFail($customerId);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'gender' => 'nullable|in:male,female,other',
            'birth_date' => 'nullable|date',
            'address' => 'nullable|string|max:1000',
        ]);

        $customer->update($validated);

        CrmCustomerTimeline::create([
            'customer_id' => $customerId,
            'action' => 'PROFILE_UPDATED',
            'description' => 'Profil berhasil diperbarui melalui Customer Portal.',
        ]);

        return back()->with('success', 'Profil Anda berhasil diperbarui!');
    }

    // =====================================
    // REDEEM VOUCHER
    // =====================================
    public function vouchers()
    {
        $customerId = Session::get('customer_portal_id');
        $customer = CrmCustomer::findOrFail($customerId);

        $availableVouchers = CrmVoucher::where('is_active', true)->get();
        $myVouchers = CrmCustomerVoucher::with('voucher')
            ->where('customer_id', $customerId)
            ->orderBy('id', 'desc')
            ->get();

        return view('portal.vouchers', compact('customer', 'availableVouchers', 'myVouchers'));
    }

    public function redeemVoucher(Request $request, $voucherId)
    {
        $customerId = Session::get('customer_portal_id');
        $customer = CrmCustomer::findOrFail($customerId);
        $voucher = CrmVoucher::findOrFail($voucherId);

        $requiredPoints = 100; // Standar poin penukaran voucher

        if ($customer->total_points < $requiredPoints) {
            return back()->withErrors(['points' => "Poin Anda tidak mencukupi untuk menukar voucher (Butuh {$requiredPoints} Pts)."]);
        }

        $customer->total_points -= $requiredPoints;
        $customer->save();

        CrmCustomerVoucher::create([
            'customer_id' => $customer->id,
            'voucher_id' => $voucher->id,
            'is_used' => false,
            'redeemed_at' => now(),
        ]);

        CrmCustomerPointHistory::create([
            'customer_id' => $customer->id,
            'points' => -$requiredPoints,
            'description' => "Redeem Voucher: {$voucher->name}",
        ]);

        CrmCustomerTimeline::create([
            'customer_id' => $customer->id,
            'action' => 'VOUCHER_REDEEMED',
            'description' => "Menukarkan {$requiredPoints} point dengan voucher '{$voucher->name}'.",
        ]);

        return back()->with('success', "Voucher '{$voucher->name}' berhasil ditukarkan!");
    }

    // =====================================
    // LOYALTY HISTORY
    // =====================================
    public function loyaltyHistory()
    {
        $customerId = Session::get('customer_portal_id');
        $customer = CrmCustomer::findOrFail($customerId);
        $histories = CrmCustomerPointHistory::where('customer_id', $customerId)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('portal.loyalty', compact('customer', 'histories'));
    }

    // =====================================
    // INVOICE HISTORY
    // =====================================
    public function invoiceHistory()
    {
        $customerId = Session::get('customer_portal_id');
        $customer = CrmCustomer::findOrFail($customerId);

        $timelines = CrmCustomerTimeline::where('customer_id', $customerId)
            ->whereIn('action', ['ORDER', 'POS_SALE', 'POINT_ADD'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('portal.invoices', compact('customer', 'timelines'));
    }

    // =====================================
    // DIGITAL MEMBERSHIP CARD
    // =====================================
    public function digitalCard()
    {
        $customerId = Session::get('customer_portal_id');
        $customer = CrmCustomer::with('tags')->findOrFail($customerId);

        return view('portal.card', compact('customer'));
    }
}
