<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\CycleCount;
use App\Models\Inventory\CycleCountLine;
use App\Models\Inventory\Warehouse;
use App\Models\Inventory\Item;
use Illuminate\Http\Request;

class CycleCountController extends Controller
{
    public function index(Request $request)
    {
        $query = CycleCount::with(['warehouse', 'lines.item']);
        if ($request->filled('search')) {
            $query->where('number', 'like', "%{$request->search}%");
        }
        $cycleCounts = $query->paginate(15);
        return view('inventory.cycle-counts.index', compact('cycleCounts'));
    }

    public function create()
    {
        $warehouses = Warehouse::all();
        $items = Item::all();
        return view('inventory.cycle-counts.create', compact('warehouses', 'items'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'number' => 'required|string|unique:inv_cycle_counts,number',
            'date' => 'required|date',
            'warehouse_id' => 'required|exists:inv_warehouses,id',
            'conducted_by' => 'required|string',
            'notes' => 'nullable|string',
            'items' => 'required|array',
            'items.*.item_id' => 'required|exists:inv_items,id',
            'items.*.expected_qty' => 'required|numeric',
            'items.*.counted_qty' => 'required|numeric',
        ]);

        $cc = CycleCount::create([
            'number' => $validated['number'],
            'date' => $validated['date'],
            'warehouse_id' => $validated['warehouse_id'],
            'conducted_by' => $validated['conducted_by'],
            'status' => 'completed',
            'notes' => $validated['notes'],
        ]);

        foreach ($request->items as $it) {
            CycleCountLine::create([
                'cycle_count_id' => $cc->id,
                'item_id' => $it['item_id'],
                'expected_qty' => $it['expected_qty'],
                'counted_qty' => $it['counted_qty'],
                'variance' => $it['counted_qty'] - $it['expected_qty'],
            ]);
        }

        return redirect()->route('inventory.cycle-counts.index')->with('success', 'Hasil Cycle Count / Stock Opname berhasil disimpan.');
    }

    public function show($id)
    {
        $cycleCount = CycleCount::with(['warehouse', 'lines.item'])->findOrFail($id);
        return view('inventory.cycle-counts.show', compact('cycleCount'));
    }

    public function destroy($id)
    {
        $cc = CycleCount::findOrFail($id);
        $cc->delete();
        return redirect()->route('inventory.cycle-counts.index')->with('success', 'Data Cycle Count dihapus.');
    }
}
