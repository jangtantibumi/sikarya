<?php

declare(strict_types=1);

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Item;
use App\Models\Inventory\Reservation;
use App\Models\Inventory\ReservationLine;
use App\Models\Inventory\Warehouse;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function index(Request $request)
    {
        $query = Reservation::with(['warehouse', 'lines.item']);
        if ($request->filled('search')) {
            $query->where('number', 'like', "%{$request->search}%")
                ->orWhere('customer_name', 'like', "%{$request->search}%");
        }
        $reservations = $query->paginate(15);

        return view('inventory.reservations.index', compact('reservations'));
    }

    public function create()
    {
        $warehouses = Warehouse::all();
        $items = Item::all();

        return view('inventory.reservations.create', compact('warehouses', 'items'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'number' => 'required|string|unique:inv_reservations,number',
            'date' => 'required|date',
            'customer_name' => 'required|string',
            'warehouse_id' => 'required|exists:inv_warehouses,id',
            'notes' => 'nullable|string',
            'items' => 'required|array',
            'items.*.item_id' => 'required|exists:inv_items,id',
            'items.*.quantity' => 'required|numeric|min:1',
        ]);

        $res = Reservation::create([
            'number' => $validated['number'],
            'date' => $validated['date'],
            'customer_name' => $validated['customer_name'],
            'warehouse_id' => $validated['warehouse_id'],
            'status' => 'reserved',
            'notes' => $validated['notes'],
        ]);

        foreach ($request->items as $it) {
            ReservationLine::create([
                'reservation_id' => $res->id,
                'item_id' => $it['item_id'],
                'quantity' => $it['quantity'],
            ]);
        }

        return redirect()->route('inventory.reservations.index')->with('success', 'Reservasi Stok berhasil dibuat.');
    }

    public function show($id)
    {
        $reservation = Reservation::with(['warehouse', 'lines.item'])->findOrFail($id);

        return view('inventory.reservations.show', compact('reservation'));
    }

    public function destroy($id)
    {
        $res = Reservation::findOrFail($id);
        $res->delete();

        return redirect()->route('inventory.reservations.index')->with('success', 'Reservasi Stok dihapus.');
    }
}
