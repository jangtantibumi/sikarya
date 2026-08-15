<?php

declare(strict_types=1);

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\BatchNumber;
use App\Models\Inventory\Item;
use App\Models\Inventory\Warehouse;
use Illuminate\Http\Request;

class BatchNumberController extends Controller
{
    public function index(Request $request)
    {
        $query = BatchNumber::with(['item', 'warehouse']);
        if ($request->filled('search')) {
            $query->where('batch_number', 'like', "%{$request->search}%");
        }
        $batches = $query->paginate(15);
        $items = Item::all();
        $warehouses = Warehouse::all();

        return view('inventory.batch-numbers.index', compact('batches', 'items', 'warehouses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:inv_items,id',
            'warehouse_id' => 'required|exists:inv_warehouses,id',
            'batch_number' => 'required|string',
            'expiry_date' => 'required|date',
            'quantity' => 'required|numeric|min:1',
        ]);
        BatchNumber::create($validated);

        return redirect()->route('inventory.batch-numbers.index')->with('success', 'Batch Number berhasil ditambahkan.');
    }

    public function destroy($id)
    {
        $bn = BatchNumber::findOrFail($id);
        $bn->delete();

        return redirect()->route('inventory.batch-numbers.index')->with('success', 'Batch Number dihapus.');
    }
}
