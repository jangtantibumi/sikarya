<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Services\TenantContext;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function __construct(private TenantContext $tenant) {}

    private function company()
    {
        abort_unless($this->tenant->id(), 422, 'Akun belum dipetakan ke perusahaan.');
        return $this->tenant->id();
    }

    public function index()
    {
        $this->company();
        return response()->json(Supplier::query()->latest()->get());
    }

    public function store(Request $request)
    {
        $companyId = $this->company();
        
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'contact_person' => 'nullable|string|max:255',
        ]);
        
        $data['company_id'] = $companyId;
        $data['code'] = 'SUP-' . now()->format('ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(4));
        
        $contactPerson = $data['contact_person'] ?? null;
        unset($data['contact_person']);
        
        $supplier = Supplier::create($data);
        
        // Create contact person record if provided
        if (!empty($contactPerson)) {
            $supplier->contacts()->create([
                'company_id' => $companyId,
                'name' => $contactPerson,
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'] ?? null,
                'is_primary' => true,
            ]);
        }
        
        return response()->json($supplier->load('contacts'), 201);
    }
        
    public function update(Request $request, $id)
    {
        $companyId = $this->company();
        $supplier = Supplier::query()->where('company_id', $companyId)->findOrFail($id);
        
        abort_unless($request->user()->isCEO(), 403, 'Hanya CEO yang dapat mengedit data Master Supplier.');
        
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'contact_person' => 'nullable|string|max:255',
        ]);
        
        $contactPerson = $data['contact_person'] ?? null;
        unset($data['contact_person']);
        
        $supplier->update($data);
        
        if (!empty($contactPerson)) {
            $supplier->contacts()->updateOrCreate(
                ['is_primary' => true],
                [
                    'company_id' => $companyId,
                    'name' => $contactPerson,
                    'phone' => $data['phone'] ?? null,
                    'email' => $data['email'] ?? null,
                ]
            );
        }
        
        return response()->json($supplier->load('contacts'));
    }
    
    public function destroy(Request $request, $id)
    {
        $companyId = $this->company();
        $supplier = Supplier::query()->where('company_id', $companyId)->findOrFail($id);
        
        abort_unless($request->user()->isCEO(), 403, 'Hanya CEO yang dapat menghapus data Master Supplier.');
        
        // Cek jika supplier sudah dipakai di PO
        if (\App\Models\PurchaseOrder::where('supplier_id', $supplier->id)->exists()) {
            return response()->json(['message' => 'Supplier tidak dapat dihapus karena sudah memiliki riwayat Purchase Order.'], 422);
        }
        
        $supplier->delete();
        
        return response()->json(['message' => 'Supplier berhasil dihapus.']);
    }
}
