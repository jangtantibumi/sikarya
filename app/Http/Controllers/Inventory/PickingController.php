<?php

declare(strict_types=1);

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Item;
use App\Models\Inventory\Picking;
use App\Models\Inventory\PickingLine;
use App\Models\Inventory\Reservation;
use App\Models\Inventory\Warehouse;
use Illuminate\Http\Request;

class PickingController extends Controller
{
    public function index(Request $request)
    {
        $query = Picking::with(['warehouse', 'reservation', 'lines.item']);
        if ($request->filled('search')) {
            $query->where('number', 'like', "%{$request->search}%")
                ->orWhere('picker_name', 'like', "%{$request->search}%");
        }
        $pickings = $query->paginate(15);

        return view('inventory.pickings.index', compact('pickings'));
    }

    public function create()
    {
        $warehouses = Warehouse::all();
        $reservations = Reservation::all();
        $items = Item::all();

        return view('inventory.pickings.create', compact('warehouses', 'reservations', 'items'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'number' => 'required|string|unique:inv_pickings,number',
            'date' => 'required|date',
            'warehouse_id' => 'required|exists:inv_warehouses,id',
            'picker_name' => 'required|string',
            'reservation_id' => 'nullable|exists:inv_reservations,id',
            'notes' => 'nullable|string',
            'items' => 'required|array',
            'items.*.item_id' => 'required|exists:inv_items,id',
            'items.*.requested_qty' => 'required|numeric|min:1',
            'items.*.picked_qty' => 'required|numeric|min:0',
        ]);

        $pic = Picking::create([
            'number' => $validated['number'],
            'date' => $validated['date'],
            'warehouse_id' => $validated['warehouse_id'],
            'picker_name' => $validated['picker_name'],
            'reservation_id' => $validated['reservation_id'] ?? null,
            'status' => 'completed',
            'notes' => $validated['notes'],
        ]);

        foreach ($request->items as $it) {
            PickingLine::create([
                'picking_id' => $pic->id,
                'item_id' => $it['item_id'],
                'requested_qty' => $it['requested_qty'],
                'picked_qty' => $it['picked_qty'],
            ]);
        }

        return redirect()->route('inventory.pickings.index')->with('success', 'Perintah Picking berhasil disimpan.');
    }

    public function show($id)
    {
        $picking = Picking::with(['warehouse', 'reservation', 'lines.item'])->findOrFail($id);

        return view('inventory.pickings.show', compact('picking'));
    }

    public function destroy($id)
    {
        $pic = Picking::findOrFail($id);
        $pic->delete();

        return redirect()->route('inventory.pickings.index')->with('success', 'Data Picking dihapus.');
    }
}
