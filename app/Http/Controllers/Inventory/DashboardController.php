<?php

declare(strict_types=1);

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Adjustment;
use App\Models\Inventory\Item;
use App\Models\Inventory\StockIn;
use App\Models\Inventory\StockMovement;
use App\Models\Inventory\StockOut;
use App\Models\Inventory\StockSummary;
use App\Models\Inventory\Transfer;
use App\Models\Inventory\Warehouse;

class DashboardController extends Controller
{
    public function index()
    {
        $totalItems = Item::count();
        $totalWarehouses = Warehouse::count();
        $totalStockQty = StockSummary::sum('quantity');
        $recentMovements = StockMovement::with(['item', 'warehouse'])->latest()->take(10)->get();

        $pendingStockIns = StockIn::where('status', 'draft')->count();
        $pendingStockOuts = StockOut::where('status', 'draft')->count();
        $pendingTransfers = Transfer::where('status', 'draft')->count();
        $pendingAdjustments = Adjustment::where('status', 'draft')->count();

        return view('inventory.dashboard', compact(
            'totalItems',
            'totalWarehouses',
            'totalStockQty',
            'recentMovements',
            'pendingStockIns',
            'pendingStockOuts',
            'pendingTransfers',
            'pendingAdjustments'
        ));
    }
}
