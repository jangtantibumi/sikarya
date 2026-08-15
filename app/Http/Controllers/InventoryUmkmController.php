<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\InventoryLedgerService;
use Illuminate\Http\Request;

class InventoryUmkmController extends Controller
{
    private function getCompanyId()
    {
        if (auth()->check() && auth()->user()->company_id) {
            return auth()->user()->company_id;
        }
        $company = Company::first();

        return $company ? $company->id : 1;
    }

    public function history()
    {
        $companyId = $this->getCompanyId();
        $movements = \App\Models\StockMovement::where('company_id', $companyId)
            ->with(['product'])
            ->orderBy('created_at', 'desc')
            ->take(100)
            ->get();

        $mapped = $movements->map(function ($m) {
            return [
                'id' => $m->id,
                'date' => $m->created_at->format('d M Y H:i'),
                'item_name' => $m->product ? $m->product->name : 'Unknown',
                'type' => $m->type,
                'quantity' => (float) $m->quantity,
                'unit' => $m->product ? $m->product->unit : '',
                'reference' => $m->reference ?? '-',
                'notes' => $m->notes ?? '-',
            ];
        });

        return response()->json($mapped);
    }

    public function index(InventoryLedgerService $inventoryService)
    {
        $companyId = $this->getCompanyId();
        $warehouse = Warehouse::where('company_id', $companyId)->first();
        $products = Product::where('company_id', $companyId)
            ->orderBy('name', 'asc')
            ->get();

        $mapped = $products->map(function ($p) use ($inventoryService, $warehouse) {
            $stock = $warehouse ? $inventoryService->balance($p, $warehouse) : 0;

            return [
                'id' => $p->id,
                'item_code' => $p->sku,
                'item_name' => $p->name,
                'category' => $p->category_id ? 'Kategori '.$p->category_id : 'Umum',
                'uom' => $p->unit,
                'min_stock' => $p->min_stock,
                'max_stock' => $p->max_stock,
                'actual_stock' => $stock,
                'total_price' => $p->standard_cost,
                'total_gram' => 0,
                'price_per_gram' => 0,
            ];
        });

        return response()->json($mapped);
    }

    public function store(Request $request, InventoryLedgerService $inventoryService)
    {
        $validated = $request->validate([
            'item_code' => 'nullable|string|unique:products,sku',
            'item_name' => 'required|string',
            'category' => 'nullable|string',
            'uom' => 'nullable|string',
            'min_stock' => 'nullable|numeric',
            'max_stock' => 'nullable|numeric',
            'actual_stock' => 'nullable|numeric',
            'total_price' => 'nullable|numeric',
        ]);

        $product = Product::create([
            'company_id' => $this->getCompanyId(),
            'sku' => $validated['item_code'] ?? 'BRG-'.strtoupper(uniqid()),
            'name' => $validated['item_name'],
            'unit' => $validated['uom'] ?? 'Pcs',
            'min_stock' => $validated['min_stock'] ?? 0,
            'max_stock' => $validated['max_stock'] ?? 0,
            'standard_cost' => $validated['total_price'] ?? 0,
            'is_active' => true,
        ]);

        $warehouse = Warehouse::where('company_id', $this->getCompanyId())->first();
        if ($warehouse && isset($validated['actual_stock']) && $validated['actual_stock'] > 0) {
            $inventoryService->move(
                $product,
                $warehouse,
                (float) $validated['actual_stock'],
                'in',
                auth()->user(),
                'STOK-AWAL',
                null, null, null,
                'Input awal stok barang UMKM'
            );
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $product->id,
                'item_code' => $product->sku,
                'item_name' => $product->name,
            ],
        ]);
    }

    public function update(Request $request, $id, InventoryLedgerService $inventoryService)
    {
        $product = Product::where('company_id', $this->getCompanyId())->findOrFail($id);

        $validated = $request->validate([
            'item_code' => ['nullable', 'string', \Illuminate\Validation\Rule::unique('products', 'sku')->ignore($id)],
            'item_name' => 'required|string',
            'category' => 'nullable|string',
            'uom' => 'nullable|string',
            'min_stock' => 'nullable|numeric',
            'max_stock' => 'nullable|numeric',
            'actual_stock' => 'nullable|numeric',
            'total_price' => 'nullable|numeric',
        ]);

        $product->update([
            'sku' => $validated['item_code'] ?? $product->sku,
            'name' => $validated['item_name'],
            'unit' => $validated['uom'] ?? $product->unit,
            'min_stock' => $validated['min_stock'] ?? $product->min_stock,
            'max_stock' => $validated['max_stock'] ?? $product->max_stock,
            'standard_cost' => $validated['total_price'] ?? $product->standard_cost,
        ]);

        $warehouse = Warehouse::where('company_id', $this->getCompanyId())->first();
        if ($warehouse && isset($validated['actual_stock'])) {
            $currentStock = $inventoryService->balance($product, $warehouse);
            $newStock = (float) $validated['actual_stock'];
            $delta = $newStock - $currentStock;
            
            if ($delta > 0) {
                $inventoryService->move(
                    $product, $warehouse, $delta, 'adjustment_in', auth()->user(), 'ADJ-IN', null, null, null, 'Koreksi penambahan stok UMKM'
                );
            } elseif ($delta < 0) {
                $inventoryService->move(
                    $product, $warehouse, $delta, 'adjustment_out', auth()->user(), 'ADJ-OUT', null, null, null, 'Koreksi pengurangan stok UMKM'
                );
            }
        }

        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        $product = Product::where('company_id', $this->getCompanyId())->findOrFail($id);
        $product->delete();

        return response()->json(['success' => true]);
    }
}
