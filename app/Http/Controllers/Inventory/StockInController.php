<?php

declare(strict_types=1);

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Item;
use App\Models\Inventory\StockIn;
use App\Models\Inventory\StockInLine;
use App\Models\Inventory\Warehouse;
use App\Services\InventoryService;
use Illuminate\Http\Request;

class StockInController extends Controller
{
    public function index(Request $request)
    {
        $query = StockIn::with(['warehouse', 'lines.item']);
        if ($request->filled('search')) {
            $query->where('number', 'like', "%{$request->search}%")
                ->orWhere('supplier_name', 'like', "%{$request->search}%");
        }
        $stockIns = $query->paginate(15);

        return view('inventory.stock-in.index', compact('stockIns'));
    }

    public function create()
    {
        $warehouses = Warehouse::all();
        $items = Item::all();

        return view('inventory.stock-in.create', compact('warehouses', 'items'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'number' => 'required|string|unique:inv_stock_ins,number',
            'date' => 'required|date',
            'supplier_name' => 'required|string',
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

        $stockIn = StockIn::create([
            'number' => $validated['number'],
            'date' => $validated['date'],
            'supplier_name' => $validated['supplier_name'],
            'warehouse_id' => $validated['warehouse_id'],
            'status' => 'draft',
            'total_amount' => $totalAmount,
            'notes' => $validated['notes'],
        ]);

        foreach ($request->items as $it) {
            StockInLine::create([
                'stock_in_id' => $stockIn->id,
                'item_id' => $it['item_id'],
                'quantity' => $it['quantity'],
                'unit_price' => $it['unit_price'],
                'total_price' => $it['quantity'] * $it['unit_price'],
            ]);
        }

        return redirect()->route('inventory.stock-in.index')->with('success', 'Transaksi Stock In berhasil disimpan sebagai Draft.');
    }

    public function show($id)
    {
        $stockIn = StockIn::with(['warehouse', 'lines.item'])->findOrFail($id);

        return view('inventory.stock-in.show', compact('stockIn'));
    }

    public function approve($id, InventoryService $service)
    {
        $stockIn = StockIn::with('lines')->findOrFail($id);
        if ($stockIn->status === 'approved') {
            return redirect()->back()->with('error', 'Transaksi sudah disetujui sebelumnya.');
        }

        foreach ($stockIn->lines as $line) {
            $service->recordMovement([
                'reference_number' => $stockIn->number,
                'transaction_type' => 'stock_in',
                'item_id' => $line->item_id,
                'warehouse_id' => $stockIn->warehouse_id,
                'bin_id' => $line->bin_id,
                'quantity' => $line->quantity,
                'unit_cost' => $line->unit_price,
                'notes' => 'Penerimaan Stock In: '.$stockIn->number,
            ]);
        }

        $stockIn->update([
            'status' => 'approved',
            'approved_by' => 'Manager Inventory',
        ]);

        return redirect()->route('inventory.stock-in.index')->with('success', 'Transaksi Stock In disetujui & stok berhasil ditambahkan.');
    }

    public function reject($id)
    {
        $stockIn = StockIn::findOrFail($id);
        $stockIn->update(['status' => 'rejected']);

        return redirect()->route('inventory.stock-in.index')->with('success', 'Transaksi Stock In ditolak.');
    }

    public function destroy($id)
    {
        $stockIn = StockIn::findOrFail($id);
        $stockIn->delete();

        return redirect()->route('inventory.stock-in.index')->with('success', 'Transaksi Stock In dihapus.');
    }
}
