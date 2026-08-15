<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Recipe;
use App\Models\RecipeItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RecipeController extends Controller
{
    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user || (!$user->isCEO() && !$user->isPlatformAdmin())) {
            abort(403, 'Hanya CEO yang memiliki wewenang membuat atau mengubah resep master.');
        }

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'name' => 'required|string|max:255',
            'yield_quantity' => 'required|numeric|min:1',
            'materials' => 'required|array|min:1',
            'materials.*' => 'required|exists:products,id',
            'quantities' => 'required|array|min:1',
            'quantities.*' => 'required|numeric|min:0.0001',
        ]);

        $companyId = $user->company_id ?? 1;

        DB::transaction(function () use ($request, $companyId, $user) {
            $recipe = Recipe::create([
                'company_id' => $companyId,
                'product_id' => $request->product_id,
                'name' => $request->name,
                'yield_quantity' => $request->yield_quantity,
                'created_by_id' => $user->id,
            ]);

            foreach ($request->materials as $index => $materialId) {
                if (!empty($materialId) && isset($request->quantities[$index]) && $request->quantities[$index] > 0) {
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
