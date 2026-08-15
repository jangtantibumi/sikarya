<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PosSession;
use App\Models\PosSale;
use App\Models\PosSaleLine;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\InventoryLedgerService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PosController extends Controller
{
    // --- BUKA/TUTUP SESI KASIR ---
    public function openSession(Request $request)
    {
        $request->validate([
            'opening_cash' => 'required|numeric|min:0',
        ]);

        $user = Auth::user();
        
        // Cek apakah user sudah punya sesi yang masih open
        $activeSession = PosSession::where('cashier_id', $user->id)
                                    ->where('status', 'open')
                                    ->first();
                                    
        if ($activeSession) {
            return redirect()->back()->withErrors(['error' => 'Anda masih memiliki sesi kasir yang terbuka. Tutup terlebih dahulu.']);
        }

        $warehouse = Warehouse::where('company_id', $user->company_id ?? 1)->first();

        PosSession::create([
            'company_id' => $user->company_id ?? 1,
            'warehouse_id' => $warehouse?->id,
            'cashier_id' => $user->id,
            'status' => 'open',
            'opening_cash' => $request->opening_cash,
            'opened_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Sesi kasir berhasil dibuka. Selamat bertugas!');
    }

    public function closeSession(Request $request)
    {
        $request->validate([
            'closing_cash' => 'required|numeric|min:0',
        ]);

        $user = Auth::user();
        
        $activeSession = PosSession::where('cashier_id', $user->id)
                                    ->where('status', 'open')
                                    ->first();
                                    
        if (!$activeSession) {
            return redirect()->back()->withErrors(['error' => 'Tidak ada sesi kasir yang terbuka.']);
        }

        $activeSession->update([
            'status' => 'closed',
            'closing_cash' => $request->closing_cash,
            'closed_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Sesi kasir berhasil ditutup. Laporan penjualan hari ini telah direkam.');
    }

    // --- TRANSAKSI PENJUALAN KASIR ---
    public function storeSale(Request $request, InventoryLedgerService $inventoryService)
    {
        $request->validate([
            'payment_method' => 'required|string',
            'items' => 'required|json', // Array of items [{product_id, quantity, unit_price}]
        ]);

        $user = Auth::user();
        
        $activeSession = PosSession::where('cashier_id', $user->id)
                                    ->where('status', 'open')
                                    ->first();
                                    
        if (!$activeSession) {
            return response()->json(['success' => false, 'message' => 'Anda harus membuka Sesi Kasir terlebih dahulu.'], 403);
        }

        $items = json_decode($request->items, true);
        if (!is_array($items) || count($items) === 0) {
            return response()->json(['success' => false, 'message' => 'Keranjang kosong.'], 422);
        }

        $companyId = $user->company_id ?? 1;
        $warehouse = Warehouse::where('company_id', $companyId)->first();
        
        if (!$warehouse) {
            return response()->json(['success' => false, 'message' => 'Gudang utama belum disetel.'], 422);
        }

        try {
            DB::transaction(function () use ($companyId, $activeSession, $request, $items, $user, $warehouse, $inventoryService) {
                // Hitung total dari lines
                $totalAmount = 0;
                
                // Buat Header Penjualan
                $sale = PosSale::create([
                    'company_id' => $companyId,
                    'pos_session_id' => $activeSession->id,
                    'receipt_number' => 'INV-' . strtoupper(Str::random(8)),
                    'total_amount' => 0, // Akan diupdate di bawah
                    'payment_method' => $request->payment_method,
                    'status' => 'paid',
                    'created_by_id' => $user->id,
                ]);

                foreach ($items as $item) {
                    $product = Product::findOrFail($item['product_id']);
                    $qty = (float) $item['quantity'];
                    $price = (float) $item['unit_price'];
                    $lineTotal = $qty * $price;
                    
                    $totalAmount += $lineTotal;

                    // Catat Baris Penjualan
                    PosSaleLine::create([
                        'company_id' => $companyId,
                        'pos_sale_id' => $sale->id,
                        'product_id' => $product->id,
                        'quantity' => $qty,
                        'unit_price' => $price,
                        'line_total' => $lineTotal,
                    ]);

                    // Potong Stok Gudang (- quantity) karena ini barang keluar (out_sale)
                    $inventoryService->move(
                        $product,
                        $warehouse,
                        -$qty,
                        'out_sale',
                        $user,
                        "POS Penjualan: {$sale->receipt_number}"
                    );
                }
                
                // Update Total Amount di Header
                $sale->update(['total_amount' => $totalAmount]);
            });

            return response()->json(['success' => true, 'message' => 'Penjualan berhasil dicatat! Stok terpotong.']);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Stok tidak cukup untuk produk yang dijual.'], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()], 500);
        }
    }
}
