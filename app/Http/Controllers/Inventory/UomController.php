<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Uom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class UomController extends Controller
{
    public function index(Request $request)
    {
        $query = Uom::withCount('items');
        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%");
        }
        $uoms = $query->paginate(15);
        return view('inventory.uoms.index', compact('uoms'));
    }

    public function create()
    {
        return view('inventory.uoms.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:inv_uoms,code',
            'symbol' => 'nullable|string|max:20',
        ]);
        Uom::create($validated);
        return redirect()->route('inventory.uoms.index')->with('success', 'UoM berhasil ditambahkan.');
    }

    public function show($id)
    {
        $uom = Uom::with('items')->findOrFail($id);
        return view('inventory.uoms.show', compact('uom'));
    }

    public function edit($id)
    {
        $uom = Uom::findOrFail($id);
        return view('inventory.uoms.edit', compact('uom'));
    }

    public function update(Request $request, $id)
    {
        $uom = Uom::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:inv_uoms,code,'.$uom->id,
            'symbol' => 'nullable|string|max:20',
        ]);
        $uom->update($validated);
        return redirect()->route('inventory.uoms.index')->with('success', 'UoM berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $uom = Uom::findOrFail($id);
        $uom->delete();
        return redirect()->route('inventory.uoms.index')->with('success', 'UoM berhasil dihapus.');
    }

    public function export()
    {
        $uoms = Uom::all();
        $csv = "ID,Code,Name,Symbol\n";
        foreach ($uoms as $u) {
            $csv .= "{$u->id},\"{$u->code}\",\"{$u->name}\",\"{$u->symbol}\"\n";
        }
        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="uoms.csv"',
        ]);
    }
}
