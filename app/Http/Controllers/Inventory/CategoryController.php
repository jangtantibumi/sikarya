<?php

declare(strict_types=1);

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::withCount('items');
        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%")
                ->orWhere('code', 'like', "%{$request->search}%");
        }
        $categories = $query->paginate(15);

        return view('inventory.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('inventory.categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:inv_categories,code',
            'description' => 'nullable|string',
        ]);
        Category::create($validated);

        return redirect()->route('inventory.categories.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function show($id)
    {
        $category = Category::with('items')->findOrFail($id);

        return view('inventory.categories.show', compact('category'));
    }

    public function edit($id)
    {
        $category = Category::findOrFail($id);

        return view('inventory.categories.edit', compact('category'));
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:inv_categories,code,'.$category->id,
            'description' => 'nullable|string',
        ]);
        $category->update($validated);

        return redirect()->route('inventory.categories.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return redirect()->route('inventory.categories.index')->with('success', 'Kategori berhasil dihapus.');
    }

    public function export()
    {
        $categories = Category::all();
        $csv = "ID,Code,Name,Description\n";
        foreach ($categories as $c) {
            $csv .= "{$c->id},\"{$c->code}\",\"{$c->name}\",\"{$c->description}\"\n";
        }

        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="categories.csv"',
        ]);
    }
}
