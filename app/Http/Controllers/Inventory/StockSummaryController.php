<?php

declare(strict_types=1);

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\StockSummary;
use App\Models\Inventory\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class StockSummaryController extends Controller
{
    public function index(Request $request)
    {
        $query = StockSummary::with(['item.category', 'warehouse', 'bin']);

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('item', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%");
            });
        }

        $stockSummaries = $query->paginate(15);
        $warehouses = Warehouse::all();

        return view('inventory.stock-summary.index', compact('stockSummaries', 'warehouses'));
    }

    public function export()
    {
        $summaries = StockSummary::with(['item', 'warehouse', 'bin'])->get();
        $csv = "Item,SKU,Warehouse,Bin,Quantity,Reserved,Allocated\n";
        foreach ($summaries as $s) {
            $csv .= "\"{$s->item->name}\",\"{$s->item->sku}\",\"{$s->warehouse->name}\",\"".optional($s->bin)->name."\",{$s->quantity},{$s->reserved_qty},{$s->allocated_qty}\n";
        }

        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="stock_summary.csv"',
        ]);
    }
}
