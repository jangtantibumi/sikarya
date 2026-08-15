<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PurchaseRequest;
use App\Models\PurchaseOrder;
use App\Models\GoodsReceipt;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Services\InventoryLedgerService;
use App\Services\AccountingService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PurchasingController extends Controller
{
    // --- PURCHASE REQUEST (PR) ---
    public function storePR(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'reason' => 'required|string',
        ]);

        $user = Auth::user();
        $isCeo = $user->isCEO();

        PurchaseRequest::create([
            'company_id' => $user->company_id ?? 1,
            'number' => 'PR-' . strtoupper(Str::random(6)),
            'title' => $request->title,
            'reason' => $request->reason,
            'status' => $isCeo ? 'approved' : 'pending_ceo',
            'requested_by_id' => $user->id,
        ]);

        return redirect()->back()->with('success', $isCeo ? 'PR berhasil dibuat & disetujui (CEO Bypass).' : 'PR berhasil diajukan. Menunggu ACC CEO.');
    }

    public function approvePR($id)
    {
        if (!Auth::user()->isCEO()) abort(403);
        $pr = PurchaseRequest::findOrFail($id);
        $pr->update(['status' => 'approved']);
        return redirect()->back()->with('success', 'Purchase Request disetujui.');
    }

    // --- PURCHASE ORDER (PO) ---
    public function storePO(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'total_amount' => 'required|numeric|min:0',
        ]);

        $user = Auth::user();
        $isCeo = $user->isCEO();

        PurchaseOrder::create([
            'company_id' => $user->company_id ?? 1,
            'supplier_id' => $request->supplier_id,
            'number' => 'PO-' . strtoupper(Str::random(6)),
            'order_date' => today(),
            'total_amount' => $request->total_amount,
            'status' => $isCeo ? 'approved' : 'pending_ceo',
            'created_by_id' => $user->id,
        ]);

        return redirect()->back()->with('success', $isCeo ? 'PO berhasil dibuat & disetujui (CEO Bypass).' : 'PO berhasil dibuat. Menunggu ACC CEO.');
    }

    public function approvePO($id)
    {
        if (!Auth::user()->isCEO()) abort(403);
        $po = PurchaseOrder::findOrFail($id);
        $po->update(['status' => 'approved']);
        // Post journal entry for approved Purchase Order
        app(AccountingService::class)->postPurchaseOrderJournal(Auth::user(), $po);
        return redirect()->back()->with('success', 'Purchase Order disetujui.');
    }

    // --- GOODS RECEIPT (GR) ---
    public function storeGR(Request $request, InventoryLedgerService $inventoryService)
    {
        $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.1',
            'unit' => 'nullable|string|in:gram,kg,pcs',
        ]);

        $multiplier = 1;
        if ($request->unit === 'kg') {
            $multiplier = 1000;
        }
        $finalQuantity = $request->quantity * $multiplier;

        $user = Auth::user();
        $isCeo = $user->isCEO();
        $companyId = $user->company_id ?? 1;
        $warehouse = Warehouse::where('company_id', $companyId)->first();
        
        if (!$warehouse) {
            return redirect()->back()->withErrors(['error' => 'Gudang utama belum dikonfigurasi.']);
        }

        DB::transaction(function () use ($request, $user, $isCeo, $companyId, $warehouse, $inventoryService) {
            $gr = GoodsReceipt::create([
                'company_id' => $companyId,
                'purchase_order_id' => $request->purchase_order_id,
                'warehouse_id' => $warehouse->id,
                'number' => 'GR-' . strtoupper(Str::random(6)),
                'received_date' => today(),
                'status' => $isCeo ? 'approved' : 'pending_ceo',
                'received_by_id' => $user->id,
            ]);

            if ($isCeo) {
                // Langsung gerakkan stok gudang jika CEO
                $product = Product::findOrFail($request->product_id);
                $inventoryService->move(
                    $product,
                    $warehouse,
                    $finalQuantity,
                    'in_purchase',
                    $user,
                    "Penerimaan Barang: {$gr->number}"
                );
            } else {
                // Simpan metadata ke notes untuk di-approve CEO nanti
                $gr->update(['notes' => json_encode(['product_id' => $request->product_id, 'quantity' => $finalQuantity])]);
            }
        });

        return redirect()->back()->with('success', $isCeo ? 'Barang diterima, stok gudang otomatis bertambah.' : 'Laporan penerimaan barang dikirim ke CEO untuk divalidasi (stok tertunda).');
    }

    public function approveGR($id, InventoryLedgerService $inventoryService)
    {
        if (!Auth::user()->isCEO()) abort(403);
        
        $gr = GoodsReceipt::findOrFail($id);
        
        DB::transaction(function () use ($gr, $inventoryService) {
            $gr->update(['status' => 'approved']);

            // Baca metadata barang tertunda dari notes
            $data = json_decode($gr->notes, true);
            if ($data && isset($data['product_id']) && isset($data['quantity'])) {
                $product = Product::findOrFail($data['product_id']);
                $warehouse = Warehouse::findOrFail($gr->warehouse_id);

                $inventoryService->move(
                    $product,
                    $warehouse,
                    $data['quantity'],
                    'in_purchase',
                    Auth::user(),
                    "Penerimaan Barang ACC: {$gr->number}"
                );
            }
        });
        // Post journal entry for approved Goods Receipt
        $metadata = ['value' => $gr->purchaseOrder->total_amount];
        app(AccountingService::class)->postGoodsReceiptJournal(Auth::user(), $gr, $metadata);

        return redirect()->back()->with('success', 'Penerimaan Barang divalidasi. Stok gudang berhasil ditambahkan!');
    }
}
