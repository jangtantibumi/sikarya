<?php

declare(strict_types=1);

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class WarehouseController extends Controller
{
    public function index(Request $request)
    {
        $query = Warehouse::with(['zones.racks.bins']);
        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%")
                ->orWhere('code', 'like', "%{$request->search}%");
        }
        $warehouses = $query->paginate(15);

        return view('inventory.warehouses.index', compact('warehouses'));
    }

    public function create()
    {
        return view('inventory.warehouses.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:inv_warehouses,code',
            'address' => 'nullable|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'manager_name' => 'nullable|string',
        ]);
        Warehouse::create($validated);

        return redirect()->route('inventory.warehouses.index')->with('success', 'Gudang berhasil ditambahkan.');
    }

    public function show($id)
    {
        $warehouse = Warehouse::with(['zones.racks.bins', 'stockSummaries.item'])->findOrFail($id);

        return view('inventory.warehouses.show', compact('warehouse'));
    }

    public function edit($id)
    {
        $warehouse = Warehouse::findOrFail($id);

        return view('inventory.warehouses.edit', compact('warehouse'));
    }

    public function update(Request $request, $id)
    {
        $warehouse = Warehouse::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:inv_warehouses,code,'.$warehouse->id,
            'address' => 'nullable|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'manager_name' => 'nullable|string',
        ]);
        $warehouse->update($validated);

        return redirect()->route('inventory.warehouses.index')->with('success', 'Gudang berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $warehouse = Warehouse::findOrFail($id);
        $warehouse->delete();

        return redirect()->route('inventory.warehouses.index')->with('success', 'Gudang berhasil dihapus.');
    }

    public function export()
    {
        $warehouses = Warehouse::all();
        $csv = "ID,Code,Name,Manager,Phone,Email,Address\n";
        foreach ($warehouses as $w) {
            $csv .= "{$w->id},\"{$w->code}\",\"{$w->name}\",\"{$w->manager_name}\",\"{$w->phone}\",\"{$w->email}\",\"{$w->address}\"\n";
        }

        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="warehouses.csv"',
        ]);
    }
}
