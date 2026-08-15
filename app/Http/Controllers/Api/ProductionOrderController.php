<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductionOrder;
use App\Models\ProductionMaterial;
use App\Models\BillOfMaterial;
use App\Models\Warehouse;
use App\Services\InventoryLedgerService;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductionOrderController extends Controller
{
    public function __construct(private TenantContext $tenant, private InventoryLedgerService $ledger) {}

    private function company(): int
    {
        abort_unless($this->tenant->id(), 422, 'Akun belum dipetakan ke perusahaan.');
        return $this->tenant->id();
    }

    public function index()
    {
        $this->company();
        return response()->json(ProductionOrder::query()->with('product:id,sku,name', 'materials.product', 'billOfMaterial')->latest()->get());
    }

    public function store(Request $r)
    {
        $c = $this->company();
        $d = $r->validate([
            'product_id' => 'required|integer',
            'bill_of_material_id' => 'nullable|integer',
            'planned_quantity' => 'required|numeric|gt:0',
            'planned_date' => 'nullable|date'
        ]);

        $p = Product::query()->findOrFail($d['product_id']);
        
        $bom = null;
        if (!empty($d['bill_of_material_id'])) {
            $bom = BillOfMaterial::query()->with('lines')->where('company_id', $c)->findOrFail($d['bill_of_material_id']);
            abort_unless($bom->product_id === $p->id, 422, 'BOM tidak sesuai dengan produk yang diproduksi.');
        }

        return DB::transaction(function () use ($c, $d, $bom, $r) {
            $d['company_id'] = $c;
            $d['number'] = 'MO-' . now()->format('Ymd') . '-' . str_pad((string)(ProductionOrder::withoutGlobalScopes()->where('company_id', $c)->count() + 1), 4, '0', STR_PAD_LEFT);
            $d['status'] = 'draft';
            $d['completed_quantity'] = 0;
            
            $mo = ProductionOrder::query()->create($d);

            if ($bom) {
                foreach ($bom->lines as $line) {
                    $qty = $line->quantity_per_unit * $d['planned_quantity'];
                    ProductionMaterial::query()->create([
                        'company_id' => $c,
                        'production_order_id' => $mo->id,
                        'product_id' => $line->component_id,
                        'planned_quantity' => $qty,
                        'actual_quantity' => $qty,
                        'status' => 'default',
                    ]);
                }
            }

            return response()->json($mo->load('materials'), 201);
        });
    }

    public function modifyMaterials(Request $r, int $id)
    {
        $mo = ProductionOrder::query()->with('materials')->findOrFail($id);
        abort_unless(in_array($mo->status, ['draft', 'released']), 422, 'Order tidak dapat dimodifikasi materialnya pada status ini.');

        $d = $r->validate([
            'materials' => 'required|array',
            'materials.*.product_id' => 'required|integer',
            'materials.*.actual_quantity' => 'required|numeric|min:0',
        ]);

        $user = $r->user();
        $isCeo = $user->isCEO();

        DB::transaction(function () use ($mo, $d, $user, $isCeo) {
            // Delete existing materials
            $mo->materials()->delete();

            // Recreate materials with new status
            foreach ($d['materials'] as $mat) {
                if ($mat['actual_quantity'] > 0) {
                    ProductionMaterial::query()->create([
                        'company_id' => $mo->company_id,
                        'production_order_id' => $mo->id,
                        'product_id' => $mat['product_id'],
                        'planned_quantity' => $mat['actual_quantity'], // or keep original, but for simplicity we set both
                        'actual_quantity' => $mat['actual_quantity'],
                        'status' => $isCeo ? 'approved' : 'pending_approval',
                        'modified_by_id' => $user->id,
                        'approved_by_id' => $isCeo ? $user->id : null,
                    ]);
                }
            }
        });

        return response()->json($mo->fresh('materials'));
    }

    public function approveMaterials(Request $r, int $id)
    {
        $mo = ProductionOrder::query()->with('materials')->findOrFail($id);
        $user = $r->user();

        $d = $r->validate([
            'decision' => 'required|in:approve,reject'
        ]);

        abort_unless($user->isManager() || $user->isCEO(), 403, 'Akses ditolak.');

        DB::transaction(function () use ($mo, $user, $d) {
            if ($d['decision'] === 'approve') {
                $newStatus = $user->isCEO() ? 'approved' : 'manager_approved';
                $mo->materials()->whereIn('status', ['pending_approval', 'manager_approved'])->update([
                    'status' => $newStatus,
                    'approved_by_id' => $user->id,
                ]);
            } else {
                // Reject reverts to default (could be deleted or marked rejected, for simplicity we revert to default BOM if needed, but let's just mark rejected)
                $mo->materials()->whereIn('status', ['pending_approval', 'manager_approved'])->update([
                    'status' => 'rejected',
                ]);
            }
        });

        return response()->json($mo->fresh('materials'));
    }

    public function release(int $id)
    {
        $o = ProductionOrder::query()->findOrFail($id);
        abort_unless($o->status === 'draft', 422, 'Order tidak dapat dirilis.');
        $o->update(['status' => 'released']);
        return response()->json($o);
    }

    public function complete(Request $r, int $id)
    {
        $mo = ProductionOrder::query()->with('materials')->findOrFail($id);
        abort_unless($mo->status === 'released', 422, 'Order harus dirilis terlebih dahulu.');
        
        // Validate all materials are approved or default
        $unapproved = $mo->materials()->whereNotIn('status', ['default', 'approved'])->count();
        if ($unapproved > 0) {
            throw ValidationException::withMessages(['materials' => 'Ada material yang belum di-ACC (pending approval atau masih di level manager).']);
        }

        $d = $r->validate([
            'warehouse_id' => 'required|integer',
            'quantity' => 'required|numeric|gt:0'
        ]);

        $w = Warehouse::query()->findOrFail($d['warehouse_id']);
        $p = Product::query()->findOrFail($mo->product_id);

        DB::transaction(function () use ($mo, $p, $w, $d, $r) {
            // 1. Backflush: Deduct all actual materials
            foreach ($mo->materials as $mat) {
                if ($mat->actual_quantity > 0) {
                    $component = Product::query()->findOrFail($mat->product_id);
                    $this->ledger->move($component, $w, -((float)$mat->actual_quantity), 'production_issue', $r->user(), $mo->number);
                }
            }

            // 2. Receipt: Add finished goods
            $this->ledger->move($p, $w, (float)$d['quantity'], 'production_receipt', $r->user(), $mo->number);

            $mo->update([
                'status' => 'completed',
                'completed_quantity' => $d['quantity']
            ]);
        });

        return response()->json($mo->fresh());
    }
}
