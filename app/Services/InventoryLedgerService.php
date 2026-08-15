<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;
use App\Models\StockBatch;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryLedgerService
{
    public function balance(Product $product, Warehouse $warehouse, ?int $batchId = null, ?int $rackId = null, ?int $binId = null): float
    {
        $query = StockMovement::query()
            ->where('product_id', $product->id)
            ->where('warehouse_id', $warehouse->id);

        if ($batchId) {
            $query->where('batch_id', $batchId);
        }
        if ($rackId) {
            $query->where('rack_id', $rackId);
        }
        if ($binId) {
            $query->where('bin_id', $binId);
        }

        return (float) $query->sum('quantity');
    }

    public function move(
        Product $product,
        Warehouse $warehouse,
        float $quantity,
        string $type,
        ?User $actor = null,
        ?string $reference = null,
        ?int $batchId = null,
        ?int $rackId = null,
        ?int $binId = null,
        ?string $notes = null
    ): StockMovement {
        return DB::transaction(function () use ($product, $warehouse, $quantity, $type, $actor, $reference, $batchId, $rackId, $binId, $notes) {
            if ($product->company_id !== $warehouse->company_id || $quantity == 0) {
                throw ValidationException::withMessages(['quantity' => 'Produk, gudang, dan jumlah tidak valid.']);
            }

            if ($quantity < 0 && $this->balance($product, $warehouse, $batchId, $rackId, $binId) + $quantity < 0) {
                throw ValidationException::withMessages(['quantity' => 'Stok tidak mencukupi; saldo tidak boleh negatif.']);
            }

            // If batchId is provided, update the StockBatch quantity
            if ($batchId) {
                $batch = StockBatch::find($batchId);
                if ($batch) {
                    $batch->quantity += $quantity;
                    $batch->save();
                }
            }

            return StockMovement::query()->create([
                'company_id' => $product->company_id,
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'quantity' => $quantity,
                'type' => $type,
                'unit_cost' => $product->standard_cost ?? 0,
                'reference' => $reference,
                'created_by_id' => $actor?->id,
                'batch_id' => $batchId,
                'rack_id' => $rackId,
                'bin_id' => $binId,
                'notes' => $notes,
            ]);
        });
    }

    public function transfer(
        Product $product,
        Warehouse $fromWarehouse,
        Warehouse $toWarehouse,
        float $quantity,
        ?User $actor = null,
        ?string $reference = null,
        ?int $batchId = null
    ) {
        return DB::transaction(function () use ($product, $fromWarehouse, $toWarehouse, $quantity, $actor, $reference, $batchId) {
            if ($quantity <= 0) {
                throw ValidationException::withMessages(['quantity' => 'Jumlah transfer harus lebih dari 0.']);
            }

            // Deduct from source
            $out = $this->move($product, $fromWarehouse, -$quantity, 'transfer_out', $actor, $reference, $batchId, null, null, 'Transfer to '.$toWarehouse->name);

            // Add to destination
            $in = $this->move($product, $toWarehouse, $quantity, 'transfer_in', $actor, $reference, $batchId, null, null, 'Transfer from '.$fromWarehouse->name);

            return ['out' => $out, 'in' => $in];
        });
    }
}
