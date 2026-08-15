<?php

namespace App\Services;

use App\Models\GoodsReceipt;
use App\Models\PurchaseOrder;
use App\Models\Warehouse;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GoodsReceiptService
{
    public function __construct(
        private InventoryLedgerService $ledger,
        private AccountingService $accounting
    ) {}

    public function receive(PurchaseOrder $po, Warehouse $warehouse, array $lines, User $actor): GoodsReceipt
    {
        return DB::transaction(function () use ($po, $warehouse, $lines, $actor) {
            if (!in_array($po->status, ['approved', 'partially_received'])) {
                throw ValidationException::withMessages(['purchase_order' => 'PO harus disetujui.']);
            }
            
            $po->load('lines.product');
            
            $receipt = GoodsReceipt::query()->create([
                'company_id' => $po->company_id,
                'purchase_order_id' => $po->id,
                'warehouse_id' => $warehouse->id,
                'number' => 'GR-' . now()->format('YmdHis') . '-' . $po->id,
                'received_date' => today(),
                'received_by_id' => $actor->id
            ]);

            $totalValue = 0;

            foreach ($lines as $line) {
                $poLine = $po->lines->firstWhere('id', $line['purchase_order_line_id']);
                
                if (!$poLine || $line['quantity'] <= 0 || $poLine->received_quantity + $line['quantity'] > $poLine->ordered_quantity) {
                    throw ValidationException::withMessages(['lines' => 'Kuantitas penerimaan tidak valid.']);
                }
                
                $receipt->lines()->create([
                    'company_id' => $po->company_id,
                    'purchase_order_line_id' => $poLine->id,
                    'received_quantity' => $line['quantity']
                ]);
                
                $poLine->increment('received_quantity', $line['quantity']);
                $this->ledger->move($poLine->product, $warehouse, $line['quantity'], 'purchase_receipt', $actor, $receipt->number);

                $totalValue += $line['quantity'] * $poLine->unit_price;
            }

            // Auto-Journaling
            if ($totalValue > 0) {
                $this->accounting->createEntry(
                    $actor,
                    $receipt->received_date,
                    "Penerimaan barang dari PO {$po->number} (Receipt: {$receipt->number})",
                    [
                        ['system_key' => 'inventory', 'debit' => $totalValue],
                        ['system_key' => 'accounts_payable', 'credit' => $totalValue]
                    ],
                    'goods_receipt',
                    $receipt->id,
                    $receipt->number
                );
            }

            $po->refresh();
            $po->update([
                'status' => $po->lines()->whereColumn('received_quantity', '<', 'ordered_quantity')->exists() ? 'partially_received' : 'received'
            ]);
            
            return $receipt;
        });
    }
}
