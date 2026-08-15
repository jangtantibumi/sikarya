<?php

declare(strict_types=1);

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Barcode;
use App\Models\Inventory\Brand;
use App\Models\Inventory\Category;
use App\Models\Inventory\Item;
use App\Models\Inventory\Uom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $query = Item::with(['category', 'brand', 'uom']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        $items = $query->paginate(15);
        $categories = Category::all();
        $brands = Brand::all();

        return view('inventory.items.index', compact('items', 'categories', 'brands'));
    }

    public function create()
    {
        $categories = Category::all();
        $brands = Brand::all();
        $uoms = Uom::all();

        return view('inventory.items.create', compact('categories', 'brands', 'uoms'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:inv_items,sku',
            'barcode' => 'nullable|string',
            'category_id' => 'required|exists:inv_categories,id',
            'brand_id' => 'required|exists:inv_brands,id',
            'uom_id' => 'required|exists:inv_uoms,id',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'min_stock' => 'required|integer|min:0',
            'max_stock' => 'required|integer|min:0',
            'reorder_point' => 'required|integer|min:0',
            'description' => 'nullable|string',
        ]);

        $item = Item::create($validated);

        if ($request->filled('barcode')) {
            Barcode::create([
                'item_id' => $item->id,
                'barcode' => $request->barcode,
                'barcode_type' => 'CODE128',
                'is_primary' => true,
            ]);
        }

        return redirect()->route('inventory.items.index')->with('success', 'Master Item berhasil ditambahkan.');
    }

    public function show($id)
    {
        $item = Item::with(['category', 'brand', 'uom', 'stockSummaries.warehouse'])->findOrFail($id);

        return view('inventory.items.show', compact('item'));
    }

    public function edit($id)
    {
        $item = Item::findOrFail($id);
        $categories = Category::all();
        $brands = Brand::all();
        $uoms = Uom::all();

        return view('inventory.items.edit', compact('item', 'categories', 'brands', 'uoms'));
    }

    public function update(Request $request, $id)
    {
        $item = Item::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:inv_items,sku,'.$item->id,
            'barcode' => 'nullable|string',
            'category_id' => 'required|exists:inv_categories,id',
            'brand_id' => 'required|exists:inv_brands,id',
            'uom_id' => 'required|exists:inv_uoms,id',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'min_stock' => 'required|integer|min:0',
            'max_stock' => 'required|integer|min:0',
            'reorder_point' => 'required|integer|min:0',
            'description' => 'nullable|string',
        ]);

        $item->update($validated);

        return redirect()->route('inventory.items.index')->with('success', 'Master Item berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $item = Item::findOrFail($id);
        $item->delete();

        return redirect()->route('inventory.items.index')->with('success', 'Master Item berhasil dihapus.');
    }

    public function export()
    {
        $items = Item::with(['category', 'brand', 'uom'])->get();
        $csvHeader = ['ID', 'SKU', 'Barcode', 'Nama Item', 'Kategori', 'Brand', 'UoM', 'Harga Beli', 'Harga Jual', 'Min Stock', 'Max Stock'];

        $csvData = [];
        $csvData[] = implode(',', $csvHeader);

        foreach ($items as $item) {
            $csvData[] = implode(',', [
                $item->id,
                '"'.$item->sku.'"',
                '"'.$item->barcode.'"',
                '"'.str_replace('"', '""', $item->name).'"',
                '"'.optional($item->category)->name.'"',
                '"'.optional($item->brand)->name.'"',
                '"'.optional($item->uom)->name.'"',
                $item->cost_price,
                $item->selling_price,
                $item->min_stock,
                $item->max_stock,
            ]);
        }

        $content = implode("\n", $csvData);

        return Response::make($content, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="inventory_items_export.csv"',
        ]);
    }

    public function import(Request $request)
    {
        return redirect()->route('inventory.items.index')->with('success', 'Fitur Import data demo siap digunakan.');
    }

    public function print($id)
    {
        $item = Item::with(['category', 'brand', 'uom'])->findOrFail($id);

        return view('inventory.items.print', compact('item'));
    }
}
