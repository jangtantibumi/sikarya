<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\BillOfMaterial;
use App\Models\BomLine;
use App\Models\Product;
use App\Models\ProductionMaterial;
use App\Models\ProductionOrder;
use App\Models\ProductionRouting;
use App\Models\ProductionRoutingStep;
use App\Models\ProductionWaste;
use App\Models\Recipe;
use App\Models\Warehouse;
use App\Services\InventoryLedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProductionController extends Controller
{
    public function autoBackflush(Request $request, InventoryLedgerService $inventoryService)
    {
        $request->validate([
            'recipe_id' => 'required|exists:recipes,id',
            'batch_quantity' => 'required|numeric|min:0.1',
        ]);

        $recipe = Recipe::with('items.product')->findOrFail($request->recipe_id);
        $companyId = Auth::user()->company_id ?? 1;

        // Ambil warehouse pertama (default) untuk perusahaan ini
        $warehouse = Warehouse::where('company_id', $companyId)->first();

        if (! $warehouse) {
            return redirect()->back()->withErrors(['error' => 'Tidak ada gudang aktif untuk melakukan backflush.']);
        }

        try {
            DB::transaction(function () use ($recipe, $request, $inventoryService, $warehouse) {
                // 1. Kurangi Bahan Baku (Raw Materials)
                foreach ($recipe->items as $item) {
                    $deductionQuantity = -($item->quantity * $request->batch_quantity);

                    // Akan throw ValidationException jika stok tidak mencukupi (diatur di InventoryLedgerService)
                    $inventoryService->move(
                        $item->product,
                        $warehouse,
                        $deductionQuantity,
                        'out_production_backflush',
                        Auth::user(),
                        "Backflush for Recipe: {$recipe->name} Batch: {$request->batch_quantity}"
                    );
                }

                // 2. Tambah Barang Jadi (Finished Goods) ke Gudang
                if ($recipe->product_id) {
                    $addedQuantity = $recipe->yield_quantity * $request->batch_quantity;
                    $inventoryService->move(
                        $recipe->product,
                        $warehouse,
                        $addedQuantity,
                        'in_production',
                        Auth::user(),
                        "Barang jadi dari Produksi: {$recipe->name} Batch: {$request->batch_quantity}"
                    );
                }
            });

            return redirect()->back()->with('success', "Produksi {$recipe->name} sebanyak {$request->batch_quantity} batch berhasil. Bahan baku terpotong dan barang jadi telah masuk ke Gudang.");

        } catch (ValidationException $e) {
            // Tangkap exception jika stok minus
            return redirect()->back()->withErrors(['error' => 'Gagal memproses produksi: Stok bahan baku tidak mencukupi untuk backflush. (Sistem mengunci stok agar tidak minus).']);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan sistem: '.$e->getMessage()]);
        }
    }

    // --- NEW: BOM & Work Order Logic ---

    public function storeBom(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'name' => 'required|string|max:255',
            'components' => 'required|array|min:1',
            'components.*.product_id' => 'required|exists:products,id',
            'components.*.quantity_per_unit' => 'required|numeric|min:0.001',
            'routings' => 'nullable|array',
            'routings.*.work_center' => 'required|string',
            'routings.*.expected_duration_minutes' => 'required|numeric|min:0',
        ]);

        $companyId = Auth::user()->company_id ?? 1;

        DB::transaction(function () use ($request, $companyId) {
            $bom = BillOfMaterial::create([
                'company_id' => $companyId,
                'product_id' => $request->product_id,
                'name' => $request->name,
                'is_active' => true,
            ]);

            foreach ($request->components as $comp) {
                BomLine::create([
                    'bill_of_material_id' => $bom->id,
                    'component_id' => $comp['product_id'],
                    'quantity_per_unit' => $comp['quantity_per_unit'],
                ]);
            }

            if ($request->has('routings') && count($request->routings) > 0) {
                $routing = ProductionRouting::create([
                    'company_id' => $companyId,
                    'bill_of_material_id' => $bom->id,
                    'name' => $bom->name.' Routing',
                ]);

                foreach ($request->routings as $index => $rStep) {
                    ProductionRoutingStep::create([
                        'production_routing_id' => $routing->id,
                        'sequence' => $index + 1,
                        'work_center' => $rStep['work_center'],
                        'expected_duration_minutes' => $rStep['expected_duration_minutes'],
                    ]);
                }
            }
        });

        return redirect()->back()->with('success', 'Bill of Material (BOM) berhasil dibuat.');
    }

    public function createWorkOrder(Request $request)
    {
        $request->validate([
            'work_orders' => 'required|array|min:1',
            'work_orders.*.bill_of_material_id' => 'required|exists:bill_of_materials,id',
            'work_orders.*.planned_quantity' => 'required|numeric|min:1',
            'planned_date' => 'required|date',
        ]);

        $companyId = Auth::user()->company_id ?? 1;

        DB::transaction(function () use ($request, $companyId) {
            foreach ($request->work_orders as $woData) {
                $bom = BillOfMaterial::with('lines')->findOrFail($woData['bill_of_material_id']);

                $poNumber = 'WO-'.date('Ymd').'-'.strtoupper(Str::random(4));

                $wo = ProductionOrder::create([
                    'company_id' => $companyId,
                    'number' => $poNumber,
                    'bill_of_material_id' => $bom->id,
                    'product_id' => $bom->product_id,
                    'planned_quantity' => $woData['planned_quantity'],
                    'status' => 'draft',
                    'planned_date' => $request->planned_date,
                ]);

                foreach ($bom->lines as $line) {
                    ProductionMaterial::create([
                        'company_id' => $companyId,
                        'production_order_id' => $wo->id,
                        'product_id' => $line->component_id,
                        'planned_quantity' => $line->quantity_per_unit * $woData['planned_quantity'],
                        'issued_quantity' => 0,
                        'actual_quantity' => 0,
                        'status' => 'requested',
                    ]);
                }
            }
        });

        return redirect()->back()->with('success', 'Work Orders berhasil dibuat.');
    }

    public function issueMaterial(Request $request, $id, InventoryLedgerService $inventoryService)
    {
        $request->validate([
            'material_id' => 'required|exists:production_materials,id',
            'quantity' => 'required|numeric|min:0.01',
        ]);

        $wo = ProductionOrder::findOrFail($id);
        $material = ProductionMaterial::where('production_order_id', $wo->id)
            ->findOrFail($request->material_id);

        $warehouse = Warehouse::where('company_id', $wo->company_id)->first();
        if (! $warehouse) {
            return redirect()->back()->withErrors(['error' => 'Tidak ada gudang aktif.']);
        }

        try {
            DB::transaction(function () use ($wo, $material, $request, $inventoryService, $warehouse) {
                $inventoryService->move(
                    $material->product,
                    $warehouse,
                    -$request->quantity,
                    'out_production_issue',
                    Auth::user(),
                    "Issue bahan WO: {$wo->number}"
                );

                $material->issued_quantity += $request->quantity;
                $material->status = 'issued';
                $material->save();
            });

            return redirect()->back()->with('success', 'Bahan baku berhasil di-issue dari gudang.');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors(['error' => 'Gagal: Stok tidak mencukupi untuk di-issue.']);
        }
    }

    public function consumeMaterial(Request $request, $id)
    {
        $request->validate([
            'material_id' => 'required|exists:production_materials,id',
            'quantity' => 'required|numeric|min:0.01',
        ]);

        $wo = ProductionOrder::findOrFail($id);
        $material = ProductionMaterial::where('production_order_id', $wo->id)
            ->findOrFail($request->material_id);

        if ($material->issued_quantity < $material->actual_quantity + $request->quantity) {
            return redirect()->back()->withErrors(['error' => 'Bahan yang dikonsumsi melebihi yang telah di-issue oleh gudang.']);
        }

        DB::transaction(function () use ($wo, $material, $request) {
            if ($wo->status === 'draft') {
                $wo->update(['status' => 'in_progress']);
            }

            $material->actual_quantity += $request->quantity;
            $material->status = 'consumed';
            $material->save();
        });

        return redirect()->back()->with('success', 'Bahan baku berhasil dikonsumsi dalam produksi.');
    }

    public function reportWaste(Request $request, $id, InventoryLedgerService $inventoryService)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.01',
            'reason' => 'required|string|max:255',
            'type' => 'required|in:waste,reject,scrap',
        ]);

        $wo = ProductionOrder::findOrFail($id);
        $warehouse = Warehouse::where('company_id', $wo->company_id)->first();
        if (! $warehouse) {
            return redirect()->back()->withErrors(['error' => 'Tidak ada gudang aktif.']);
        }

        try {
            DB::transaction(function () use ($wo, $request, $inventoryService, $warehouse) {
                $product = Product::findOrFail($request->product_id);

                $inventoryService->move(
                    $product,
                    $warehouse,
                    -$request->quantity,
                    'out_production_waste',
                    Auth::user(),
                    "Waste WO: {$wo->number} - {$request->reason}"
                );

                ProductionWaste::create([
                    'company_id' => $wo->company_id,
                    'production_order_id' => $wo->id,
                    'product_id' => $product->id,
                    'quantity' => $request->quantity,
                    'type' => $request->type,
                    'reason' => $request->reason,
                ]);
            });

            return redirect()->back()->with('success', 'Waste produksi berhasil dicatat.');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors(['error' => 'Gagal: Stok tidak mencukupi untuk mencatat waste ini.']);
        }
    }

    public function completeWorkOrder(Request $request, $id, InventoryLedgerService $inventoryService)
    {
        $request->validate([
            'completed_quantity' => 'required|numeric|min:0.1',
        ]);

        $wo = ProductionOrder::findOrFail($id);
        if ($wo->status === 'completed') {
            return redirect()->back()->withErrors(['error' => 'Work Order ini sudah diselesaikan.']);
        }

        $warehouse = Warehouse::where('company_id', $wo->company_id)->first();
        if (! $warehouse) {
            return redirect()->back()->withErrors(['error' => 'Tidak ada gudang aktif.']);
        }

        DB::transaction(function () use ($wo, $request, $inventoryService, $warehouse) {
            // Tambah barang jadi ke gudang
            $inventoryService->move(
                $wo->product,
                $warehouse,
                $request->completed_quantity,
                'in_production',
                Auth::user(),
                "Penyelesaian WO: {$wo->number}"
            );

            $wo->completed_quantity = $request->completed_quantity;
            $wo->status = 'completed';
            $wo->save();
        });

        return redirect()->back()->with('success', 'Work Order berhasil diselesaikan dan barang jadi telah ditambahkan ke gudang.');
    }
}
