<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Mail\CeoLoginNotificationMail;
use App\Models\Company;
use App\Models\GoodsReceipt;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\CompanyFeatureManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class MasterProductDemoController extends Controller
{
    public function __construct(private readonly CompanyFeatureManager $features) {}

    public function index()
    {
        $this->localOnly();
        $user = auth()->user();

        // Redirect directly to the dashboard, skipping the tenant selection
        if ($user->isCEO() || $user->isPlatformAdmin()) {
            return redirect()->route('master-demo.app');
        }

        return redirect()->route('master-demo.employee');
    }

    public function login()
    {
        $this->localOnly();

        return view('master-demo-login');
    }

    public function authenticate(Request $request)
    {
        $this->localOnly();
        $credentials = $request->validate(['username' => ['required', 'string'], 'password' => ['required', 'string']]);
        $user = User::query()->where('username', $credentials['username'])->where('is_active', true)->first();
        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors(['username' => 'Username atau password demo tidak valid.'])->onlyInput('username');
        }
        Auth::login($user);
        $request->session()->regenerate();

        // F-008: Trigger email notification for sensitive/CEO login
        try {
            $ceoEmail = config('mail.admin_address', $user->email ?? 'ceo@suba-erp.local');
            if ($ceoEmail) {
                Mail::to($ceoEmail)->send(new CeoLoginNotificationMail($user));
            }
        } catch (\Throwable $e) {
            Log::warning('Email notification trigger skipped: '.$e->getMessage());
        }

        // Role-based redirect: CEO/admin → executive workspace, others → employee dashboard
        if ($user->isCEO() || $user->isPlatformAdmin()) {
            return redirect()->route('master-demo.app');
        }

        return redirect()->route('master-demo.employee');
    }

    public function logout(Request $request)
    {
        $this->localOnly();
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('master-demo.login');
    }

    public function purchasing()
    {
        $this->localOnly();
        $companies = Company::query()->orderBy('id')->get();
        $company = $companies->firstWhere('id', request()->integer('company')) ?? $companies->firstOrFail();

        return view('master-demo-purchasing', [
            'companies' => $companies,
            'company' => $company,
            'suppliers' => Supplier::withoutGlobalScopes()->where('company_id', $company->id)->get(),
            'orders' => PurchaseOrder::withoutGlobalScopes()->where('company_id', $company->id)->with('lines.product')->latest()->get(),
            'receipts' => GoodsReceipt::withoutGlobalScopes()->where('company_id', $company->id)->latest()->get(),
            'products' => Product::withoutGlobalScopes()->where('company_id', $company->id)->get(),
            'warehouses' => Warehouse::withoutGlobalScopes()->where('company_id', $company->id)->get(),
            'currentUser' => auth()->user(),
        ]);
    }

    public function updateMaterial(Request $request)
    {
        $this->localOnly();

        $request->validate([
            'material_id' => 'required|exists:products,id',
            'min_stock' => 'required|numeric|min:0',
            'max_stock' => 'nullable|numeric|min:0',
            'standard_cost' => 'required|numeric|min:0',
        ]);

        $product = Product::findOrFail($request->material_id);
        $product->update([
            'min_stock' => $request->min_stock,
            'max_stock' => $request->max_stock,
            'standard_cost' => $request->standard_cost,
        ]);

        return redirect()->back()->with('success', 'Batas Stok dan Harga Baku berhasil diperbarui.');
    }

    public function updateFeature(Request $request, Company $company, string $feature)
    {
        $this->localOnly();
        $validated = $request->validate(['state' => ['required', 'in:active,read_only,off']]);

        try {
            $this->features->set($company, $feature, $validated['state']);

            return back()->with('demo_notice', 'Status modul diperbarui untuk '.$company->name.'.');
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }
    }

    private function localOnly(): void
    {
        abort_unless(app()->environment(['local', 'testing']), 404);
    }
}
