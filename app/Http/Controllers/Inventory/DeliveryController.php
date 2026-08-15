<?php

declare(strict_types=1);

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Delivery;
use App\Models\Inventory\DeliveryLine;
use App\Models\Inventory\Item;
use App\Models\Inventory\Packing;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    public function index(Request $request)
    {
        $query = Delivery::with(['packing', 'lines.item']);
        if ($request->filled('search')) {
            $query->where('number', 'like', "%{$request->search}%")
                ->orWhere('courier_name', 'like', "%{$request->search}%")
                ->orWhere('tracking_number', 'like', "%{$request->search}%");
        }
        $deliveries = $query->paginate(15);

        return view('inventory.deliveries.index', compact('deliveries'));
    }

    public function create()
    {
        $packings = Packing::all();
        $items = Item::all();

        return view('inventory.deliveries.create', compact('packings', 'items'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'number' => 'required|string|unique:inv_deliveries,number',
            'date' => 'required|date',
            'courier_name' => 'required|string',
            'tracking_number' => 'nullable|string',
            'delivery_address' => 'required|string',
            'packing_id' => 'nullable|exists:inv_packings,id',
            'notes' => 'nullable|string',
            'items' => 'required|array',
            'items.*.item_id' => 'required|exists:inv_items,id',
            'items.*.delivered_qty' => 'required|numeric|min:1',
        ]);

        $del = Delivery::create([
            'number' => $validated['number'],
            'date' => $validated['date'],
            'courier_name' => $validated['courier_name'],
            'tracking_number' => $validated['tracking_number'] ?? 'TRK-'.rand(10000, 99999),
            'delivery_address' => $validated['delivery_address'],
            'packing_id' => $validated['packing_id'] ?? null,
            'status' => 'shipped',
            'notes' => $validated['notes'],
        ]);

        foreach ($request->items as $it) {
            DeliveryLine::create([
                'delivery_id' => $del->id,
                'item_id' => $it['item_id'],
                'delivered_qty' => $it['delivered_qty'],
            ]);
        }

        return redirect()->route('inventory.deliveries.index')->with('success', 'Pengiriman Delivery berhasil dijadwalkan.');
    }

    public function show($id)
    {
        $delivery = Delivery::with(['packing', 'lines.item'])->findOrFail($id);

        return view('inventory.deliveries.show', compact('delivery'));
    }

    public function destroy($id)
    {
        $del = Delivery::findOrFail($id);
        $del->delete();

        return redirect()->route('inventory.deliveries.index')->with('success', 'Data Delivery dihapus.');
    }
}
