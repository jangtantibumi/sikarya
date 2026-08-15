<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Transfer;
use App\Models\Inventory\TransferLine;
use App\Models\Inventory\Warehouse;
use App\Models\Inventory\Item;
use App\Services\InventoryService;
use Illuminate\Http\Request;

class TransferController extends Controller
{
    public function index(Request $request)
    {
        $query = Transfer::with(['sourceWarehouse', 'destinationWarehouse', 'lines.item']);
        if ($request->filled('search')) {
            $query->where('number', 'like', "%{$request->search}%");
        }
        $transfers = $query->paginate(15);
        return view('inventory.transfers.index', compact('transfers'));
    }

    public function create()
    {
        $warehouses = Warehouse::all();
        $items = Item::all();
        return view('inventory.transfers.create', compact('warehouses', 'items'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'number' => 'required|string|unique:inv_transfers,number',
            'date' => 'required|date',
            'source_warehouse_id' => 'required|exists:inv_warehouses,id',
            'destination_warehouse_id' => 'required|exists:inv_warehouses,id|different:source_warehouse_id',
            'notes' => 'nullable|string',
            'items' => 'required|array',
            'items.*.item_id' => 'required|exists:inv_items,id',
            'items.*.quantity' => 'required|numeric|min:1',
        ]);

        $transfer = Transfer::create([
            'number' => $validated['number'],
            'date' => $validated['date'],
            'source_warehouse_id' => $validated['source_warehouse_id'],
            'destination_warehouse_id' => $validated['destination_warehouse_id'],
            'status' => 'draft',
            'notes' => $validated['notes'],
        ]);

        foreach ($request->items as $it) {
            TransferLine::create([
                'transfer_id' => $transfer->id,
                'item_id' => $it['item_id'],
                'quantity' => $it['quantity'],
            ]);
        }

        return redirect()->route('inventory.transfers.index')->with('success', 'Transfer barang berhasil dicatat.');
    }

    public function show($id)
    {
        $transfer = Transfer::with(['sourceWarehouse', 'destinationWarehouse', 'lines.item'])->findOrFail($id);
        return view('inventory.transfers.show', compact('transfer'));
    }

    public function approve($id, InventoryService $service)
    {
        $transfer = Transfer::with('lines')->findOrFail($id);
        if ($transfer->status === 'approved') {
            return redirect()->back()->with('error', 'Transfer sudah disetujui.');
        }

        foreach ($transfer->lines as $line) {
            // Deduct source
            $service->recordMovement([
                'reference_number' => $transfer->number,
                'transaction_type' => 'transfer_out',
                'item_id'          => $line->item_id,
                'warehouse_id'     => $transfer->source_warehouse_id,
                'quantity'         => -$line->quantity,
                'notes'            => 'Transfer keluar ke ' . $transfer->destinationWarehouse->name,
            ]);

            // Add destination
            $service->recordMovement([
                'reference_number' => $transfer->number,
                'transaction_type' => 'transfer_in',
                'item_id'          => $line->item_id,
                'warehouse_id'     => $transfer->destination_warehouse_id,
                'quantity'         => $line->quantity,
                'notes'            => 'Transfer masuk dari ' . $transfer->sourceWarehouse->name,
            ]);
        }

        $transfer->update(['status' => 'approved', 'approved_by' => 'Logistics Admin']);
        return redirect()->route('inventory.transfers.index')->with('success', 'Transfer barang berhasil disetujui & stok berpindah.');
    }

    public function reject($id)
    {
        $transfer = Transfer::findOrFail($id);
        $transfer->update(['status' => 'rejected']);
        return redirect()->route('inventory.transfers.index')->with('success', 'Transfer barang ditolak.');
    }

    public function destroy($id)
    {
        $transfer = Transfer::findOrFail($id);
        $transfer->delete();
        return redirect()->route('inventory.transfers.index')->with('success', 'Transfer barang dihapus.');
    }
}
