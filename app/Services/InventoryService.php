<?php

namespace App\Services;

use App\Models\Inventory\StockSummary;
use App\Models\Inventory\StockMovement;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Record a stock movement and update summary
     */
    public function recordMovement(array $data)
    {
        return DB::transaction(function () use ($data) {
            $movement = StockMovement::create([
                'reference_number' => $data['reference_number'] ?? 'REF-'.time(),
                'transaction_type' => $data['transaction_type'],
                'item_id'          => $data['item_id'],
                'warehouse_id'     => $data['warehouse_id'],
                'bin_id'           => $data['bin_id'] ?? null,
                'quantity'         => $data['quantity'],
                'unit_cost'        => $data['unit_cost'] ?? 0,
                'total_cost'       => ($data['quantity'] * ($data['unit_cost'] ?? 0)),
                'notes'            => $data['notes'] ?? null,
                'created_by'       => $data['created_by'] ?? 'System Admin',
            ]);

            // Update Stock Summary
            $summary = StockSummary::firstOrCreate(
                [
                    'item_id'      => $data['item_id'],
                    'warehouse_id' => $data['warehouse_id'],
                    'bin_id'       => $data['bin_id'] ?? null,
                ],
                [
                    'quantity'     => 0,
                    'reserved_qty' => 0,
                    'allocated_qty'=> 0,
                ]
            );

            $summary->quantity += $data['quantity'];
            if ($summary->quantity < 0) {
                // Prevent negative stock if required, or keep non-negative floor
                $summary->quantity = max(0, $summary->quantity);
            }
            $summary->save();

            return $movement;
        });
    }
}
