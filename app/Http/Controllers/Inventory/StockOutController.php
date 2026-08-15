<?php

declare(strict_types=1);

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Item;
use App\Models\Inventory\StockOut;
use App\Models\Inventory\StockOutLine;
use App\Models\Inventory\Warehouse;
use App\Services\InventoryService;
use Illuminate\Http\Request;

class StockOutController extends Controller
{
    public function index(Request $request)
    {
        $query = StockOut::with(['warehouse', 'lines.item']);
        if ($request->filled('search')) {
            $query->where('number', 'like', "%{$request->search}%")
                ->orWhere('recipient_name', 'like', "%{$request->search}%");
        }
        $stockOuts = $query->paginate(15);

        return view('inventory.stock-out.index', compact('stockOuts'));
    }

    public function create()
    {
        $warehouses = Warehouse::all();
        $items = Item::all();

        return view('inventory.stock-out.create', compact('warehouses', 'items'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'number' => 'required|string|unique:inv_stock_outs,number',
            'date' => 'required|date',
            'recipient_name' => 'required|string',
            'warehouse_id' => 'required|exists:inv_warehouses,id',
            'notes' => 'nullable|string',
            'items' => 'required|array',
            'items.*.item_id' => 'required|exists:inv_items,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $totalAmount = 0;
        foreach ($request->items as $it) {
            $totalAmount += $it['quantity'] * $it['unit_price'];
        }

        $stockOut = StockOut::create([
            'number' => $validated['number'],
            'date' => $validated['date'],
            'recipient_name' => $validated['recipient_name'],
            'warehouse_id' => $validated['warehouse_id'],
            'status' => 'draft',
            'total_amount' => $totalAmount,
            'notes' => $validated['notes'],
        ]);

        foreach ($request->items as $it) {
            StockOutLine::create([
                'stock_out_id' => $stockOut->id,
                'item_id' => $it['item_id'],
                'quantity' => $it['quantity'],
                'unit_price' => $it['unit_price'],
                'total_price' => $it['quantity'] * $it['unit_price'],
            ]);
        }

        return redirect()->route('inventory.stock-out.index')->with('success', 'Transaksi Stock Out berhasil disimpan sebagai Draft.');
    }

    public function show($id)
    {
        $stockOut = StockOut::with(['warehouse', 'lines.item'])->findOrFail($id);

        return view('inventory.stock-out.show', compact('stockOut'));
    }

    public function approve($id, InventoryService $service)
    {
        $stockOut = StockOut::with('lines')->findOrFail($id);
        if ($stockOut->status === 'approved') {
            return redirect()->back()->with('error', 'Transaksi sudah disetujui sebelumnya.');
        }

        foreach ($stockOut->lines as $line) {
            $service->recordMovement([
                'reference_number' => $stockOut->number,
                'transaction_type' => 'stock_out',
                'item_id' => $line->item_id,
                'warehouse_id' => $stockOut->warehouse_id,
                'bin_id' => $line->bin_id,
                'quantity' => -$line->quantity, // negative movement for stock out
                'unit_cost' => $line->unit_price,
                'notes' => 'Pengeluaran Stock Out: '.$stockOut->number,
            ]);
        }

        $stockOut->update([
            'status' => 'approved',
            'approved_by' => 'Manager Inventory',
        ]);

        return redirect()->route('inventory.stock-out.index')->with('success', 'Transaksi Stock Out disetujui & stok berhasil dikurangi.');
    }

    public function reject($id)
    {
        $stockOut = StockOut::findOrFail($id);
        $stockOut->update(['status' => 'rejected']);

        return redirect()->route('inventory.stock-out.index')->with('success', 'Transaksi Stock Out ditolak.');
    }

    public function destroy($id)
    {
        $stockOut = StockOut::findOrFail($id);
        $stockOut->delete();

        return redirect()->route('inventory.stock-out.index')->with('success', 'Transaksi Stock Out dihapus.');
    }
}
