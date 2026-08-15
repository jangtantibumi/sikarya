<?php

namespace App\Http\Controllers;

use App\Models\CrmCustomer;
use App\Models\CrmCustomerTimeline;
use App\Models\CrmCustomerPointHistory;
use App\Models\CrmTag;
use App\Models\CrmSegment;
use App\Services\CrmCustomerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CrmController extends Controller
{
    protected $customerService;

    public function __construct(CrmCustomerService $customerService)
    {
        $this->customerService = $customerService;
    }

    // =====================
    // CRM Dashboard
    // =====================
    public function dashboard(Request $request)
    {
        $totalCustomers = CrmCustomer::count();
        $newCustomers = CrmCustomer::whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year)->count();
        $repeatCustomers = CrmCustomer::whereHas('timelines', function ($q) {
            $q->whereIn('action', ['ORDER', 'POS_SALE', 'POINT_ADD']);
        }, '>=', 2)->count();
        $totalSpending = CrmCustomer::sum('total_spending') ?: 0;

        $topCustomers = CrmCustomer::orderBy('total_spending', 'desc')->take(5)->get();

        $membershipDistribution = CrmCustomer::select('membership_level', DB::raw('count(*) as total'))
            ->groupBy('membership_level')
            ->get()
            ->keyBy('membership_level');

        $customersThisYear = CrmCustomer::whereYear('created_at', now()->year)->get();
        $growthData = collect();
        foreach (range(1, 12) as $m) {
            $count = $customersThisYear->filter(function($c) use ($m) {
                return $c->created_at && $c->created_at->month == $m;
            })->count();
            
            if ($count > 0) {
                $growthData->push((object)[
                    'month' => $m,
                    'year' => now()->year,
                    'total' => $count
                ]);
            }
        }

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'data' => compact('totalCustomers', 'newCustomers', 'repeatCustomers', 'totalSpending', 'topCustomers', 'membershipDistribution', 'growthData')
            ]);
        }

        return view('crm.dashboard', compact(
            'totalCustomers', 'newCustomers', 'repeatCustomers', 'totalSpending',
            'topCustomers', 'membershipDistribution', 'growthData'
        ));
    }

    // =====================
    // Customer List
    // =====================
    public function index(Request $request)
    {
        $query = CrmCustomer::with(['tags', 'segment']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('customer_code', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('referral_code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('membership_level')) {
            $query->where('membership_level', $request->membership_level);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        if ($request->filled('is_blacklisted')) {
            $query->where('is_blacklisted', $request->is_blacklisted);
        }

        if ($request->filled('tag_id')) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->where('crm_tags.id', $request->tag_id);
            });
        }

        $allowedSorts = ['name', 'customer_code', 'total_spending', 'total_points', 'membership_level', 'created_at'];
        $sortColumn = in_array($request->get('sort_by'), $allowedSorts) ? $request->get('sort_by') : 'created_at';
        $sortDirection = $request->get('sort_dir') === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortColumn, $sortDirection);

        if ($request->wantsJson()) {
            $customers = $query->paginate($request->get('per_page', 10));
            return response()->json(['status' => 'success', 'data' => $customers]);
        }

        $customers = $query->paginate(10);
        $tags = CrmTag::all();
        $segments = CrmSegment::all();

        return view('crm.customers.index', compact('customers', 'tags', 'segments'));
    }

    // =====================
    // Customer Create
    // =====================
    public function create()
    {
        $tags = CrmTag::all();
        $segments = CrmSegment::all();
        return view('crm.customers.create', compact('tags', 'segments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'phone'      => 'required|string|max:20',
            'email'      => 'nullable|email|max:255',
            'gender'     => 'nullable|in:male,female,other',
            'birth_date' => 'nullable|date',
            'address'    => 'nullable|string|max:1000',
            'notes'      => 'nullable|string|max:2000',
            'referral_code_used' => 'nullable|string',
            'tags'       => 'nullable|array',
            'tags.*'     => 'exists:crm_tags,id',
            'segment_id' => 'nullable|exists:crm_segments,id',
        ]);

        $customer = CrmCustomer::create($validated);

        if (!empty($validated['tags'])) {
            $customer->tags()->sync($validated['tags']);
        }

        // Proses rujukan referral jika ada
        if (!empty($validated['referral_code_used'])) {
            app(\App\Services\CrmMarketingService::class)->processReferral($customer, $validated['referral_code_used']);
        }

        CrmCustomerTimeline::create([
            'customer_id' => $customer->id,
            'action' => 'CREATED',
            'description' => "Customer terdaftar dengan kode {$customer->customer_code}.",
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Customer berhasil dibuat',
                'data'    => $customer->load('tags')
            ], 201);
        }

        return redirect()->route('crm.customers.index')
            ->with('success', 'Customer ' . $customer->name . ' berhasil ditambahkan dengan kode ' . $customer->customer_code);
    }

    // =====================
    // Customer Detail / Show
    // =====================
    public function show(Request $request, $id)
    {
        $customer = CrmCustomer::with([
            'timelines' => fn($q) => $q->orderBy('created_at', 'desc'),
            'pointHistories' => fn($q) => $q->orderBy('created_at', 'desc'),
            'tags',
            'segment',
            'referredBy',
            'referrals.referee',
        ])->findOrFail($id);

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'data' => $customer]);
        }

        $allCustomers = CrmCustomer::where('id', '!=', $customer->id)->get();

        return view('crm.customers.show', compact('customer', 'allCustomers'));
    }

    // =====================
    // Customer Edit
    // =====================
    public function edit($id)
    {
        $customer = CrmCustomer::with('tags')->findOrFail($id);
        $tags = CrmTag::all();
        $segments = CrmSegment::all();
        return view('crm.customers.edit', compact('customer', 'tags', 'segments'));
    }

    public function update(Request $request, $id)
    {
        $customer = CrmCustomer::findOrFail($id);

        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'phone'      => 'required|string|max:20',
            'email'      => 'nullable|email|max:255',
            'gender'     => 'nullable|in:male,female,other',
            'birth_date' => 'nullable|date',
            'address'    => 'nullable|string|max:1000',
            'notes'      => 'nullable|string|max:2000',
            'is_active'  => 'nullable|boolean',
            'membership_level' => 'nullable|string',
            'segment_id' => 'nullable|exists:crm_segments,id',
            'tags'       => 'nullable|array',
            'tags.*'     => 'exists:crm_tags,id',
        ]);

        $oldLevel = $customer->membership_level;
        $customer->update($validated);

        if (isset($validated['tags'])) {
            $customer->tags()->sync($validated['tags']);
        }

        $customer->refresh();

        CrmCustomerTimeline::create([
            'customer_id' => $customer->id,
            'action'      => 'UPDATE',
            'description' => 'Data customer diperbarui',
        ]);

        if ($oldLevel !== $customer->membership_level) {
            CrmCustomerTimeline::create([
                'customer_id' => $customer->id,
                'action'      => 'MEMBERSHIP_UPGRADE',
                'description' => "Membership naik dari {$oldLevel} ke {$customer->membership_level}",
            ]);
        }

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Customer berhasil diperbarui', 'data' => $customer]);
        }

        return redirect()->route('crm.customers.show', $customer->id)
            ->with('success', 'Customer berhasil diperbarui');
    }

    // =====================
    // Customer Delete
    // =====================
    public function destroy(Request $request, $id)
    {
        $customer = CrmCustomer::findOrFail($id);
        $customer->delete();

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Customer berhasil dihapus']);
        }

        return redirect()->route('crm.customers.index')
            ->with('success', 'Customer berhasil dihapus');
    }

    // =====================
    // Blacklist Customer
    // =====================
    public function toggleBlacklist(Request $request, $id)
    {
        $customer = CrmCustomer::findOrFail($id);

        $validated = $request->validate([
            'blacklist_reason' => 'nullable|string|max:500',
        ]);

        $customer->is_blacklisted = !$customer->is_blacklisted;
        $customer->blacklist_reason = $customer->is_blacklisted ? ($validated['blacklist_reason'] ?? 'Di-blacklist oleh admin') : null;
        $customer->save();

        $actionText = $customer->is_blacklisted ? 'BLACKLIST_ADDED' : 'BLACKLIST_REMOVED';
        $descText = $customer->is_blacklisted ? "Customer dimasukkan ke blacklist. Alasan: {$customer->blacklist_reason}" : "Customer dikeluarkan dari blacklist.";

        CrmCustomerTimeline::create([
            'customer_id' => $customer->id,
            'action' => $actionText,
            'description' => $descText,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => $descText,
                'is_blacklisted' => $customer->is_blacklisted,
            ]);
        }

        return back()->with('success', $descText);
    }

    // =====================
    // Merge Duplicate Customers
    // =====================
    public function mergeDuplicatesForm()
    {
        $customers = CrmCustomer::orderBy('name', 'asc')->get();
        return view('crm.customers.merge', compact('customers'));
    }

    public function mergeDuplicates(Request $request)
    {
        $validated = $request->validate([
            'source_id' => 'required|exists:crm_customers,id',
            'target_id' => 'required|exists:crm_customers,id|different:source_id',
        ]);

        $target = $this->customerService->mergeCustomers($validated['source_id'], $validated['target_id']);

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Customer duplikat berhasil digabungkan.',
                'data' => $target,
            ]);
        }

        return redirect()->route('crm.customers.show', $target->id)
            ->with('success', 'Customer duplikat berhasil digabungkan.');
    }

    // =====================
    // Customer Tag Management
    // =====================
    public function storeTag(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:crm_tags,name',
            'color' => 'required|string|max:20',
        ]);

        $tag = CrmTag::create($validated);

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'data' => $tag]);
        }

        return back()->with('success', "Tag '{$tag->name}' berhasil dibuat.");
    }

    // =====================
    // Loyalty Points
    // =====================
    public function addPoint(Request $request, $id)
    {
        $customer = CrmCustomer::findOrFail($id);

        $validated = $request->validate([
            'points'      => 'required|integer|min:1',
            'description' => 'required|string|max:255',
        ]);

        $customer->total_points += $validated['points'];
        $customer->save();

        CrmCustomerPointHistory::create([
            'customer_id' => $customer->id,
            'points'      => $validated['points'],
            'description' => $validated['description'],
        ]);

        CrmCustomerTimeline::create([
            'customer_id' => $customer->id,
            'action'      => 'POINT_ADD',
            'description' => "Point ditambahkan: +{$validated['points']} — {$validated['description']}",
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'status'       => 'success',
                'message'      => "Berhasil menambah {$validated['points']} point",
                'total_points' => $customer->total_points,
            ]);
        }

        return back()->with('success', "Berhasil menambah {$validated['points']} point");
    }

    public function redeemPoint(Request $request, $id)
    {
        $customer = CrmCustomer::findOrFail($id);

        $validated = $request->validate([
            'points'      => 'required|integer|min:1',
            'description' => 'required|string|max:255',
        ]);

        if ($customer->total_points < $validated['points']) {
            if ($request->wantsJson()) {
                return response()->json(['status' => 'error', 'message' => 'Point tidak mencukupi'], 422);
            }
            return back()->withErrors(['points' => 'Point tidak mencukupi untuk redeem']);
        }

        $customer->total_points -= $validated['points'];
        $customer->save();

        CrmCustomerPointHistory::create([
            'customer_id' => $customer->id,
            'points'      => -$validated['points'],
            'description' => $validated['description'],
        ]);

        CrmCustomerTimeline::create([
            'customer_id' => $customer->id,
            'action'      => 'POINT_REDEEM',
            'description' => "Point diredeem: -{$validated['points']} — {$validated['description']}",
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'status'       => 'success',
                'message'      => "Berhasil redeem {$validated['points']} point",
                'total_points' => $customer->total_points,
            ]);
        }

        return back()->with('success', "Berhasil redeem {$validated['points']} point");
    }

    // =====================
    // Export CSV / Excel
    // =====================
    public function exportCsv(Request $request)
    {
        $query = CrmCustomer::query();
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('name', 'like', "%$s%")->orWhere('customer_code', 'like', "%$s%")->orWhere('phone', 'like', "%$s%"));
        }
        if ($request->filled('membership_level')) $query->where('membership_level', $request->membership_level);
        if ($request->filled('is_active')) $query->where('is_active', $request->is_active);

        $customers = $query->get();

        return $this->customerService->exportExcel($customers);
    }

    public function exportExcel(Request $request)
    {
        return $this->exportCsv($request);
    }

    public function exportPdf(Request $request)
    {
        $query = CrmCustomer::query();
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('name', 'like', "%$s%")->orWhere('customer_code', 'like', "%$s%")->orWhere('phone', 'like', "%$s%"));
        }
        if ($request->filled('membership_level')) $query->where('membership_level', $request->membership_level);
        if ($request->filled('is_active')) $query->where('is_active', $request->is_active);

        $customers = $query->get();

        return view('crm.customers.export_pdf', compact('customers'));
    }

    // =====================
    // Import CSV
    // =====================
    public function importCsv(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $file = $request->file('import_file');
        $handle = fopen($file->getPathname(), 'r');
        
        $header = fgetcsv($handle);
        $importedCount = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) >= 2) {
                CrmCustomer::create([
                    'name' => $row[0] ?? 'Unknown',
                    'phone' => $row[1] ?? '',
                    'email' => !empty($row[2]) ? $row[2] : null,
                    'gender' => in_array(strtolower($row[3] ?? ''), ['male', 'female', 'other']) ? strtolower($row[3]) : null,
                    'birth_date' => !empty($row[4]) ? $row[4] : null,
                    'address' => $row[5] ?? null,
                    'notes' => $row[6] ?? null,
                ]);
                $importedCount++;
            }
        }
        fclose($handle);

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => "Berhasil mengimpor $importedCount data customer."]);
        }

        return redirect()->back()->with('success', "Berhasil mengimpor $importedCount data customer.");
    }
}
