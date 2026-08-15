<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Barcode;
use App\Models\Inventory\Item;
use Illuminate\Http\Request;

class BarcodeController extends Controller
{
    public function index(Request $request)
    {
        $query = Barcode::with('item');
        if ($request->filled('search')) {
            $query->where('barcode', 'like', "%{$request->search}%");
        }
        $barcodes = $query->paginate(15);
        $items = Item::all();
        return view('inventory.barcodes.index', compact('barcodes', 'items'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:inv_items,id',
            'barcode' => 'required|string|unique:inv_barcodes,barcode',
            'barcode_type' => 'required|string',
        ]);
        Barcode::create($validated);
        return redirect()->route('inventory.barcodes.index')->with('success', 'Barcode berhasil dibuat.');
    }

    public function destroy($id)
    {
        $bc = Barcode::findOrFail($id);
        $bc->delete();
        return redirect()->route('inventory.barcodes.index')->with('success', 'Barcode dihapus.');
    }
}
