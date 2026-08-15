<?php

declare(strict_types=1);

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Adjustment;
use App\Models\Inventory\AdjustmentLine;
use App\Models\Inventory\Item;
use App\Models\Inventory\Warehouse;
use App\Services\InventoryService;
use Illuminate\Http\Request;

class AdjustmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Adjustment::with(['warehouse', 'lines.item']);
        if ($request->filled('search')) {
            $query->where('number', 'like', "%{$request->search}%");
        }
        $adjustments = $query->paginate(15);

        return view('inventory.adjustments.index', compact('adjustments'));
    }

    public function create()
    {
        $warehouses = Warehouse::all();
        $items = Item::all();

        return view('inventory.adjustments.create', compact('warehouses', 'items'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'number' => 'required|string|unique:inv_adjustments,number',
            'date' => 'required|date',
            'warehouse_id' => 'required|exists:inv_warehouses,id',
            'type' => 'required|in:addition,reduction',
            'notes' => 'nullable|string',
            'items' => 'required|array',
            'items.*.item_id' => 'required|exists:inv_items,id',
            'items.*.adjustment_qty' => 'required|numeric',
            'items.*.reason' => 'nullable|string',
        ]);

        $adj = Adjustment::create([
            'number' => $validated['number'],
            'date' => $validated['date'],
            'warehouse_id' => $validated['warehouse_id'],
            'type' => $validated['type'],
            'status' => 'draft',
            'notes' => $validated['notes'],
        ]);

        foreach ($request->items as $it) {
            AdjustmentLine::create([
                'adjustment_id' => $adj->id,
                'item_id' => $it['item_id'],
                'system_qty' => 100,
                'actual_qty' => 100 + $it['adjustment_qty'],
                'adjustment_qty' => $it['adjustment_qty'],
                'reason' => $it['reason'] ?? 'Adjustment manual',
            ]);
        }

        return redirect()->route('inventory.adjustments.index')->with('success', 'Stock Adjustment dicatat sebagai Draft.');
    }

    public function show($id)
    {
        $adjustment = Adjustment::with(['warehouse', 'lines.item'])->findOrFail($id);

        return view('inventory.adjustments.show', compact('adjustment'));
    }

    public function approve($id, InventoryService $service)
    {
        $adj = Adjustment::with('lines')->findOrFail($id);
        if ($adj->status === 'approved') {
            return redirect()->back()->with('error', 'Adjustment sudah disetujui.');
        }

        foreach ($adj->lines as $line) {
            $service->recordMovement([
                'reference_number' => $adj->number,
                'transaction_type' => 'adjustment',
                'item_id' => $line->item_id,
                'warehouse_id' => $adj->warehouse_id,
                'quantity' => $line->adjustment_qty,
                'notes' => 'Stock Adjustment ('.$adj->type.'): '.$line->reason,
            ]);
        }

        $adj->update(['status' => 'approved', 'approved_by' => 'Head of Auditor']);

        return redirect()->route('inventory.adjustments.index')->with('success', 'Adjustment disetujui & stok berhasil diperbarui.');
    }

    public function reject($id)
    {
        $adj = Adjustment::findOrFail($id);
        $adj->update(['status' => 'rejected']);

        return redirect()->route('inventory.adjustments.index')->with('success', 'Adjustment ditolak.');
    }

    public function destroy($id)
    {
        $adj = Adjustment::findOrFail($id);
        $adj->delete();

        return redirect()->route('inventory.adjustments.index')->with('success', 'Adjustment dihapus.');
    }
}
