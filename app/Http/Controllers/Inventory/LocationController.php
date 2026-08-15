<?php

declare(strict_types=1);

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Warehouse;
use App\Models\Inventory\WarehouseBin;
use App\Models\Inventory\WarehouseRack;
use App\Models\Inventory\WarehouseZone;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index(Request $request)
    {
        $warehouses = Warehouse::with(['zones.racks.bins'])->paginate(10);

        return view('inventory.locations.index', compact('warehouses'));
    }

    public function create()
    {
        $warehouses = Warehouse::all();
        $zones = WarehouseZone::all();
        $racks = WarehouseRack::all();

        return view('inventory.locations.create', compact('warehouses', 'zones', 'racks'));
    }

    public function store(Request $request)
    {
        $type = $request->input('location_type', 'zone');

        if ($type === 'zone') {
            $request->validate([
                'warehouse_id' => 'required|exists:inv_warehouses,id',
                'name' => 'required|string',
                'code' => 'required|string',
            ]);
            WarehouseZone::create([
                'warehouse_id' => $request->warehouse_id,
                'name' => $request->name,
                'code' => $request->code,
                'description' => $request->description,
            ]);
        } elseif ($type === 'rack') {
            $request->validate([
                'zone_id' => 'required|exists:inv_warehouse_zones,id',
                'name' => 'required|string',
                'code' => 'required|string',
            ]);
            WarehouseRack::create([
                'zone_id' => $request->zone_id,
                'name' => $request->name,
                'code' => $request->code,
            ]);
        } else {
            $request->validate([
                'rack_id' => 'required|exists:inv_warehouse_racks,id',
                'name' => 'required|string',
                'code' => 'required|string',
            ]);
            WarehouseBin::create([
                'rack_id' => $request->rack_id,
                'name' => $request->name,
                'code' => $request->code,
            ]);
        }

        return redirect()->route('inventory.locations.index')->with('success', 'Lokasi gudang berhasil ditambahkan.');
    }

    public function destroyBin($id)
    {
        $bin = WarehouseBin::findOrFail($id);
        $bin->delete();

        return redirect()->route('inventory.locations.index')->with('success', 'Bin lokasi berhasil dihapus.');
    }
}
