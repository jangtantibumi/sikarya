<?php

namespace App\Http\Controllers;

use App\Models\CrmVoucher;
use Illuminate\Http\Request;

class CrmVoucherController extends Controller
{
    public function index()
    {
        $vouchers = CrmVoucher::orderBy('id', 'desc')->get();
        return view('crm.vouchers.index', compact('vouchers'));
    }

    public function create()
    {
        return view('crm.vouchers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:crm_vouchers',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'min_purchase' => 'required|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active') ? true : false;

        CrmVoucher::create($validated);
        return redirect()->route('crm.vouchers.index')->with('success', 'Voucher berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $voucher = CrmVoucher::findOrFail($id);
        return view('crm.vouchers.edit', compact('voucher'));
    }

    public function update(Request $request, $id)
    {
        $voucher = CrmVoucher::findOrFail($id);
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:crm_vouchers,code,' . $id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'min_purchase' => 'required|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active') ? true : false;

        $voucher->update($validated);
        return redirect()->route('crm.vouchers.index')->with('success', 'Voucher berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $voucher = CrmVoucher::findOrFail($id);
        $voucher->delete();
        return redirect()->route('crm.vouchers.index')->with('success', 'Voucher berhasil dihapus.');
    }
}
