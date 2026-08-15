<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Item;
use App\Models\Inventory\Warehouse;
use App\Models\Inventory\StockSummary;
use App\Models\Inventory\StockMovement;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $topItems = StockSummary::with('item')
            ->selectRaw('item_id, SUM(quantity) as total_qty')
            ->groupBy('item_id')
            ->orderBy('total_qty', 'desc')
            ->take(10)
            ->get();

        $warehouseSummaries = Warehouse::withCount('stockSummaries')->get();
        $totalValuation = StockSummary::join('inv_items', 'inv_stock_summaries.item_id', '=', 'inv_items.id')
            ->selectRaw('SUM(inv_stock_summaries.quantity * inv_items.cost_price) as total_value')
            ->value('total_value');

        return view('inventory.reports.index', compact('topItems', 'warehouseSummaries', 'totalValuation'));
    }
}
