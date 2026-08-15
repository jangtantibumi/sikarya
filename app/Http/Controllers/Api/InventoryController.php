<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\Category;
use App\Models\Brand;
use App\Models\StockMovement;
use App\Models\StockBatch;
use App\Models\WarehouseRack;
use App\Models\WarehouseBin;
use App\Models\InventoryAudit;
use App\Services\InventoryLedgerService;
use App\Services\TenantContext;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function __construct(private TenantContext $tenant, private InventoryLedgerService $ledger) {}

    private function company(): Company
    {
        abort_unless($this->tenant->id(), 422, 'Akun belum dipetakan ke perusahaan.');
        return Company::findOrFail($this->tenant->id());
    }

    public function index(Request $request)
    {
        $company = $this->company();
        $warehouses = Warehouse::query()->get();
        
        $query = Product::query()->with(['category', 'brand']);
        
        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }
        
        if ($cat = $request->input('category_id')) $query->where('category_id', $cat);
        if ($brand = $request->input('brand_id')) $query->where('brand_id', $brand);
        
        $products = $query->paginate($request->input('per_page', 15));
        
        // Append balances to items
        $products->getCollection()->transform(function($p) use ($warehouses) {
            return [
                'id' => $p->id,
                'sku' => $p->sku,
                'name' => $p->name,
                'category' => $p->category?->name,
                'brand' => $p->brand?->name,
                'unit' => $p->unit,
                'reorder_level' => $p->reorder_level,
                'balances' => $warehouses->map(fn($w) => [
                    'warehouse' => $w->name,
                    'quantity' => $this->ledger->balance($p, $w)
                ])
            ];
        });

        return response()->json([
            'warehouses' => $warehouses,
            'products' => $products,
            'categories' => Category::query()->get(),
            'brands' => Brand::query()->get(),
        ]);
    }

    public function storeProduct(Request $request)
    {
        $this->company();
        $data = $request->validate([
            'sku' => 'required|string|max:80',
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|integer|exists:categories,id',
            'brand_id' => 'nullable|integer|exists:brands,id',
            'unit' => 'nullable|string|max:20',
            'barcode' => 'nullable|string|max:100',
            'reorder_level' => 'nullable|numeric|min:0',
            'min_stock' => 'nullable|numeric|min:0',
            'max_stock' => 'nullable|numeric|min:0',
            'standard_cost' => 'nullable|numeric|min:0',
            'has_batches' => 'boolean',
            'has_serial_numbers' => 'boolean',
        ]);
        
        return response()->json(Product::query()->create($data), 201);
    }
    
    public function updateProduct(Request $request, $id)
    {
        $this->company();
        $product = Product::findOrFail($id);
        
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|integer|exists:categories,id',
            'brand_id' => 'nullable|integer|exists:brands,id',
            'unit' => 'nullable|string|max:20',
            'barcode' => 'nullable|string|max:100',
            'reorder_level' => 'nullable|numeric|min:0',
            'min_stock' => 'nullable|numeric|min:0',
            'max_stock' => 'nullable|numeric|min:0',
            'has_batches' => 'boolean',
            'has_serial_numbers' => 'boolean',
        ]);
        
        $product->update($data);
        return response()->json($product);
    }

    public function move(Request $request)
    {
        $this->company();
        $data = $request->validate([
            'product_id' => 'required|integer',
            'warehouse_id' => 'required|integer',
            'quantity' => 'required|numeric|not_in:0',
            'type' => 'required|string|max:40',
            'reference' => 'nullable|string|max:100',
            'batch_id' => 'nullable|integer|exists:stock_batches,id',
            'rack_id' => 'nullable|integer|exists:warehouse_racks,id',
            'bin_id' => 'nullable|integer|exists:warehouse_bins,id',
            'notes' => 'nullable|string',
        ]);
        
        $product = Product::query()->findOrFail($data['product_id']);
        $warehouse = Warehouse::query()->findOrFail($data['warehouse_id']);
        
        return response()->json($this->ledger->move(
            $product, 
            $warehouse, 
            (float) $data['quantity'], 
            $data['type'], 
            $request->user(), 
            $data['reference'] ?? null,
            $data['batch_id'] ?? null,
            $data['rack_id'] ?? null,
            $data['bin_id'] ?? null,
            $data['notes'] ?? null
        ), 201);
    }

    public function transfer(Request $request)
    {
        $this->company();
        $data = $request->validate([
            'product_id' => 'required|integer',
            'from_warehouse_id' => 'required|integer',
            'to_warehouse_id' => 'required|integer|different:from_warehouse_id',
            'quantity' => 'required|numeric|min:0.001',
            'reference' => 'nullable|string|max:100',
            'batch_id' => 'nullable|integer|exists:stock_batches,id',
        ]);
        
        $product = Product::findOrFail($data['product_id']);
        $from = Warehouse::findOrFail($data['from_warehouse_id']);
        $to = Warehouse::findOrFail($data['to_warehouse_id']);
        
        return response()->json($this->ledger->transfer(
            $product,
            $from,
            $to,
            (float) $data['quantity'],
            $request->user(),
            $data['reference'] ?? null,
            $data['batch_id'] ?? null
        ), 201);
    }
    
    public function stockCard(Request $request, $productId)
    {
        $this->company();
        
        $query = StockMovement::query()
            ->with(['warehouse', 'batch', 'rack', 'bin'])
            ->where('product_id', $productId)
            ->orderBy('created_at', 'desc');
            
        if ($wh = $request->input('warehouse_id')) {
            $query->where('warehouse_id', $wh);
        }
        
        return response()->json($query->paginate($request->input('per_page', 20)));
    }
    
    // Master Data Endpoints
    public function storeCategory(Request $request) {
        $this->company();
        $data = $request->validate(['name' => 'required|string|max:255', 'description' => 'nullable|string']);
        return response()->json(Category::create($data), 201);
    }
    
    public function storeBrand(Request $request) {
        $this->company();
        $data = $request->validate(['name' => 'required|string|max:255', 'description' => 'nullable|string']);
        return response()->json(Brand::create($data), 201);
    }
}
