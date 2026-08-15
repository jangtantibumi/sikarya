<?php

declare(strict_types=1);

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Item;
use App\Models\Inventory\SerialNumber;
use App\Models\Inventory\Warehouse;
use Illuminate\Http\Request;

class SerialNumberController extends Controller
{
    public function index(Request $request)
    {
        $query = SerialNumber::with(['item', 'warehouse']);
        if ($request->filled('search')) {
            $query->where('serial_number', 'like', "%{$request->search}%");
        }
        $serials = $query->paginate(15);
        $items = Item::all();
        $warehouses = Warehouse::all();

        return view('inventory.serial-numbers.index', compact('serials', 'items', 'warehouses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:inv_items,id',
            'warehouse_id' => 'required|exists:inv_warehouses,id',
            'serial_number' => 'required|string|unique:inv_serial_numbers,serial_number',
            'status' => 'required|string',
        ]);
        SerialNumber::create($validated);

        return redirect()->route('inventory.serial-numbers.index')->with('success', 'Serial Number berhasil dicatat.');
    }

    public function destroy($id)
    {
        $sn = SerialNumber::findOrFail($id);
        $sn->delete();

        return redirect()->route('inventory.serial-numbers.index')->with('success', 'Serial Number dihapus.');
    }
}
