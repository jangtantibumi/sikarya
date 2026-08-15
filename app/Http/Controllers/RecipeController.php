<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Models\RecipeItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RecipeController extends Controller
{
    public function store(Request $request)
    {
        $user = Auth::user();
        if (! $user || (! $user->isCEO() && ! $user->isPlatformAdmin())) {
            abort(403, 'Hanya CEO yang memiliki wewenang membuat atau mengubah resep master.');
        }

        $request->validate([
            'new_product_name' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'yield_quantity' => 'required|numeric|min:1',
            'materials' => 'required|array|min:1',
            'materials.*' => 'required|exists:products,id',
            'quantities' => 'required|array|min:1',
            'quantities.*' => 'required|numeric|min:0.0001',
        ]);

        $companyId = $user->company_id ?? 1;

        DB::transaction(function () use ($request, $companyId, $user) {
            // Magic: Otomatis daftarkan Barang Jadi ke Gudang
            $product = \App\Models\Product::create([
                'company_id' => $companyId,
                'name' => $request->new_product_name,
                'type' => 'finished_good',
                'sku' => 'FG-' . strtoupper(\Illuminate\Support\Str::random(6)),
                'unit' => 'pcs',
                'is_active' => true,
            ]);

            $recipe = Recipe::create([
                'company_id' => $companyId,
                'product_id' => $product->id,
                'name' => $request->name,
                'yield_quantity' => $request->yield_quantity,
                'created_by_id' => $user->id,
            ]);

            foreach ($request->materials as $index => $materialId) {
                if (! empty($materialId) && isset($request->quantities[$index]) && $request->quantities[$index] > 0) {
                    RecipeItem::create([
                        'recipe_id' => $recipe->id,
                        'product_id' => $materialId,
                        'quantity' => $request->quantities[$index],
                        'unit' => 'gram',
                    ]);
                }
            }
        });

        return redirect()->back()->with('success', 'Resep (BOM) baru berhasil disimpan.');
    }
}
