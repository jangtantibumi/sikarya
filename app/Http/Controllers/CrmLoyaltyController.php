<?php

namespace App\Http\Controllers;

use App\Models\CrmLoyalty;
use Illuminate\Http\Request;

class CrmLoyaltyController extends Controller
{
    public function index()
    {
        $loyalties = CrmLoyalty::all();
        return view('crm.loyalties.index', compact('loyalties'));
    }

    public function create()
    {
        return view('crm.loyalties.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'rule_name' => 'required|string|max:255',
            'spending_amount' => 'required|numeric|min:0',
            'points_awarded' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);
        
        $validated['is_active'] = $request->has('is_active') ? true : false;

        CrmLoyalty::create($validated);
        return redirect()->route('crm.loyalties.index')->with('success', 'Loyalty rule berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $loyalty = CrmLoyalty::findOrFail($id);
        return view('crm.loyalties.edit', compact('loyalty'));
    }

    public function update(Request $request, $id)
    {
        $loyalty = CrmLoyalty::findOrFail($id);
        $validated = $request->validate([
            'rule_name' => 'required|string|max:255',
            'spending_amount' => 'required|numeric|min:0',
            'points_awarded' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);
        
        $validated['is_active'] = $request->has('is_active') ? true : false;

        $loyalty->update($validated);
        return redirect()->route('crm.loyalties.index')->with('success', 'Loyalty rule berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $loyalty = CrmLoyalty::findOrFail($id);
        $loyalty->delete();
        return redirect()->route('crm.loyalties.index')->with('success', 'Loyalty rule berhasil dihapus.');
    }
}
