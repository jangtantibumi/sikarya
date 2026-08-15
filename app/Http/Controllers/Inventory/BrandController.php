<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        $query = Brand::withCount('items');
        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%");
        }
        $brands = $query->paginate(15);
        return view('inventory.brands.index', compact('brands'));
    }

    public function create()
    {
        return view('inventory.brands.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:inv_brands,code',
            'description' => 'nullable|string',
        ]);
        Brand::create($validated);
        return redirect()->route('inventory.brands.index')->with('success', 'Brand berhasil ditambahkan.');
    }

    public function show($id)
    {
        $brand = Brand::with('items')->findOrFail($id);
        return view('inventory.brands.show', compact('brand'));
    }

    public function edit($id)
    {
        $brand = Brand::findOrFail($id);
        return view('inventory.brands.edit', compact('brand'));
    }

    public function update(Request $request, $id)
    {
        $brand = Brand::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:inv_brands,code,'.$brand->id,
            'description' => 'nullable|string',
        ]);
        $brand->update($validated);
        return redirect()->route('inventory.brands.index')->with('success', 'Brand berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $brand = Brand::findOrFail($id);
        $brand->delete();
        return redirect()->route('inventory.brands.index')->with('success', 'Brand berhasil dihapus.');
    }

    public function export()
    {
        $brands = Brand::all();
        $csv = "ID,Code,Name,Description\n";
        foreach ($brands as $b) {
            $csv .= "{$b->id},\"{$b->code}\",\"{$b->name}\",\"{$b->description}\"\n";
        }
        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="brands.csv"',
        ]);
    }
}
