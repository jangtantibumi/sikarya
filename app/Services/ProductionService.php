<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Recipe;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ProductionService
{
    /**
     * Complete a production run. Add finished goods to stock and backflush raw materials based on the recipe.
     */
    public function recordProduction(Recipe $recipe, float $producedQuantity, Warehouse $warehouse, User $user, string $reference)
    {
        if ($producedQuantity <= 0) {
            throw new InvalidArgumentException('Produced quantity must be greater than zero.');
        }

        DB::transaction(function () use ($recipe, $producedQuantity, $warehouse, $user, $reference) {
            // 1. Add finished goods to stock
            StockMovement::create([
                'company_id' => $recipe->company_id,
                'product_id' => $recipe->product_id,
                'warehouse_id' => $warehouse->id,
                'type' => 'production_in',
                'quantity' => $producedQuantity,
                'unit_cost' => $recipe->product->standard_cost ?? 0,
                'reference' => $reference,
                'created_by_id' => $user->id,
            ]);

            // 2. Backflush raw materials (Deduct from stock)
            $multiplier = $producedQuantity / $recipe->yield_quantity;

            foreach ($recipe->items as $item) {
                $deductQuantity = $item->quantity * $multiplier;

                StockMovement::create([
                    'company_id' => $recipe->company_id,
                    'product_id' => $item->product_id,
                    'warehouse_id' => $warehouse->id,
                    'type' => 'production_out', // consumption
                    'quantity' => -$deductQuantity, // Negative for deduction
                    'unit_cost' => $item->product->standard_cost ?? 0,
                    'reference' => $reference,
                    'created_by_id' => $user->id,
                ]);
            }
        });
    }
}
