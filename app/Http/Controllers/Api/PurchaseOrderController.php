<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function __construct(private TenantContext $tenant) {}

    private function company()
    {
        abort_unless($this->tenant->id(), 422, 'Akun belum dipetakan ke perusahaan.');

        return $this->tenant->id();
    }

    public function index()
    {
        $this->company();

        return response()->json(PurchaseOrder::query()->with(['lines.product', 'supplier'])->latest()->get());
    }

    public function store(Request $r)
    {
        $c = $this->company();
        $d = $r->validate(['supplier_id' => 'required|integer', 'expected_date' => 'nullable|date', 'lines' => 'required|array|min:1', 'lines.*.product_id' => 'required|integer', 'lines.*.quantity' => 'required|numeric|gt:0', 'lines.*.unit_price' => 'required|numeric|min:0']);
        $s = Supplier::query()->findOrFail($d['supplier_id']);
        $po = PurchaseOrder::query()->create(['company_id' => $c, 'supplier_id' => $s->id, 'number' => 'PO-'.now()->format('YmdHis'), 'status' => 'draft', 'order_date' => today(), 'expected_date' => $d['expected_date'] ?? null, 'total_amount' => 0, 'created_by_id' => $r->user()->id]);
        $total = 0;
        foreach ($d['lines'] as $l) {
            $p = Product::query()->findOrFail($l['product_id']);
            $line = $l['quantity'] * $l['unit_price'];
            $po->lines()->create(['company_id' => $c, 'product_id' => $p->id, 'ordered_quantity' => $l['quantity'], 'unit_price' => $l['unit_price'], 'line_total' => $line]);
            $total += $line;
        }$po->update(['total_amount' => $total]);

        return response()->json($po->fresh(['lines.product', 'supplier']), 201);
    }

    public function update(Request $r, int $id)
    {
        $c = $this->company();
        $po = PurchaseOrder::query()->where('company_id', $c)->findOrFail($id);
        abort_unless(in_array($po->status, ['draft', 'submitted']), 403, 'PO hanya dapat diedit pada status draft atau submitted.');
        abort_unless($r->user()->isCEO() || ($po->created_by_id === $r->user()->id && $po->status === 'draft'), 403, 'Anda tidak memiliki hak untuk mengedit PO ini.');
        $d = $r->validate(['supplier_id' => 'required|integer', 'expected_date' => 'nullable|date', 'lines' => 'required|array|min:1', 'lines.*.product_id' => 'required|integer', 'lines.*.quantity' => 'required|numeric|gt:0', 'lines.*.unit_price' => 'required|numeric|min:0']);
        DB::transaction(function () use ($d, $po, $c) {
            $po->update(['supplier_id' => $d['supplier_id'], 'expected_date' => $d['expected_date'] ?? null]);
            $po->lines()->delete();
            $total = 0;
            foreach ($d['lines'] as $l) {
                $p = Product::query()->findOrFail($l['product_id']);
                $line = $l['quantity'] * $l['unit_price'];
                $po->lines()->create(['company_id' => $c, 'product_id' => $p->id, 'ordered_quantity' => $l['quantity'], 'unit_price' => $l['unit_price'], 'line_total' => $line]);
                $total += $line;
            } $po->update(['total_amount' => $total]);
        });

        return response()->json($po->fresh(['lines.product', 'supplier']));
    }

    public function submit(Request $r, int $id)
    {
        $po = PurchaseOrder::query()->findOrFail($id);
        abort_unless($po->created_by_id === $r->user()->id && $po->status === 'draft', 403);
        $po->update(['status' => 'submitted', 'submitted_at' => now()]);

        return response()->json($po);
    }

    public function decide(Request $r, int $id)
    {
        $po = PurchaseOrder::query()->findOrFail($id);
        abort_unless($po->status === 'submitted' && $po->created_by_id !== $r->user()->id, 403);
        $d = $r->validate(['decision' => 'required|in:approved,rejected', 'reason' => 'nullable|string|max:1000']);
        $ceoRequired = $po->total_amount > 10000000;
        abort_unless($ceoRequired ? $r->user()->isCEO() : ($r->user()->isManager() || $r->user()->isCEO()), 403);
        $po->update(['status' => $d['decision'], 'approved_by_id' => $r->user()->id, 'approved_at' => now(), 'rejected_reason' => $d['decision'] === 'rejected' ? $d['reason'] : null]);

        return response()->json($po);
    }
}
