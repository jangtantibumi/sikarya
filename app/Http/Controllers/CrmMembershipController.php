<?php

namespace App\Http\Controllers;

use App\Models\CrmMembership;
use Illuminate\Http\Request;

class CrmMembershipController extends Controller
{
    public function index()
    {
        $memberships = CrmMembership::orderBy('min_points')->get();
        return view('crm.memberships.index', compact('memberships'));
    }

    public function create()
    {
        return view('crm.memberships.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:crm_memberships',
            'min_points' => 'required|integer|min:0',
            'discount_percentage' => 'required|numeric|min:0|max:100',
            'benefits' => 'nullable|string',
            'color_badge' => 'required|string|max:50',
        ]);

        CrmMembership::create($validated);
        return redirect()->route('crm.memberships.index')->with('success', 'Membership tier berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $membership = CrmMembership::findOrFail($id);
        return view('crm.memberships.edit', compact('membership'));
    }

    public function update(Request $request, $id)
    {
        $membership = CrmMembership::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:crm_memberships,name,' . $id,
            'min_points' => 'required|integer|min:0',
            'discount_percentage' => 'required|numeric|min:0|max:100',
            'benefits' => 'nullable|string',
            'color_badge' => 'required|string|max:50',
        ]);

        $membership->update($validated);
        return redirect()->route('crm.memberships.index')->with('success', 'Membership tier berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $membership = CrmMembership::findOrFail($id);
        $membership->delete();
        return redirect()->route('crm.memberships.index')->with('success', 'Membership tier berhasil dihapus.');
    }
}
