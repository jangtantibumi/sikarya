<?php

declare(strict_types=1);

namespace Modules\MasterData\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Shift;
use App\Models\Supplier;
use App\Models\User;

class MasterDataController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $stats = [
            'products' => Product::count(),
            'suppliers' => Supplier::count(),
            'users' => User::count(),
            'shifts' => Shift::count(),
        ];

        return view('masterdata::index', compact('stats'));
    }
}
