<?php

declare(strict_types=1);

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Item;
use App\Models\Inventory\Packing;
use App\Models\Inventory\PackingLine;
use App\Models\Inventory\Picking;
use Illuminate\Http\Request;

class PackingController extends Controller
{
    public function index(Request $request)
    {
        $query = Packing::with(['picking', 'lines.item']);
        if ($request->filled('search')) {
            $query->where('number', 'like', "%{$request->search}%")
                ->orWhere('packer_name', 'like', "%{$request->search}%");
        }
        $packings = $query->paginate(15);

        return view('inventory.packings.index', compact('packings'));
    }

    public function create()
    {
        $pickings = Picking::all();
        $items = Item::all();

        return view('inventory.packings.create', compact('pickings', 'items'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'number' => 'required|string|unique:inv_packings,number',
            'date' => 'required|date',
            'packer_name' => 'required|string',
            'picking_id' => 'nullable|exists:inv_pickings,id',
            'notes' => 'nullable|string',
            'items' => 'required|array',
            'items.*.item_id' => 'required|exists:inv_items,id',
            'items.*.packed_qty' => 'required|numeric|min:1',
            'items.*.box_number' => 'nullable|string',
        ]);

        $pac = Packing::create([
            'number' => $validated['number'],
            'date' => $validated['date'],
            'packer_name' => $validated['packer_name'],
            'picking_id' => $validated['picking_id'] ?? null,
            'status' => 'packed',
            'notes' => $validated['notes'],
        ]);

        foreach ($request->items as $it) {
            PackingLine::create([
                'packing_id' => $pac->id,
                'item_id' => $it['item_id'],
                'packed_qty' => $it['packed_qty'],
                'box_number' => $it['box_number'] ?? 'BOX-01',
            ]);
        }

        return redirect()->route('inventory.packings.index')->with('success', 'Pengemasan Packing berhasil disimpan.');
    }

    public function show($id)
    {
        $packing = Packing::with(['picking', 'lines.item'])->findOrFail($id);

        return view('inventory.packings.show', compact('packing'));
    }

    public function destroy($id)
    {
        $pac = Packing::findOrFail($id);
        $pac->delete();

        return redirect()->route('inventory.packings.index')->with('success', 'Data Packing dihapus.');
    }
}
