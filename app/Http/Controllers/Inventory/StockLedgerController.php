<?php

declare(strict_types=1);

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\StockMovement;
use App\Models\Inventory\Warehouse;
use Illuminate\Http\Request;

class StockLedgerController extends Controller
{
    public function index(Request $request)
    {
        $query = StockMovement::with(['item', 'warehouse', 'bin']);

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reference_number', 'like', "%{$search}%")
                    ->orWhereHas('item', function ($iq) use ($search) {
                        $iq->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%");
                    });
            });
        }

        $movements = $query->latest()->paginate(20);
        $warehouses = Warehouse::all();

        return view('inventory.stock-ledger.index', compact('movements', 'warehouses'));
    }
}
