<?php

declare(strict_types=1);

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Category;
use App\Models\Inventory\StockMovement;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $movementsByMonth = StockMovement::selectRaw("strftime('%Y-%m', created_at) as month, transaction_type, COUNT(*) as total_trans")
            ->groupBy('month', 'transaction_type')
            ->get();

        $categoryStock = Category::withCount('items')->get();

        return view('inventory.analytics.index', compact('movementsByMonth', 'categoryStock'));
    }
}
