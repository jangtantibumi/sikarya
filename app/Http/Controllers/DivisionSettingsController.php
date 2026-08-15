<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\CompanyDivision;
use App\Models\CompanyFeature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DivisionSettingsController extends Controller
{
    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $company = $request->user()->company ?? Company::first();

        $division = CompanyDivision::create([
            'company_id' => $company->id,
            'name' => $request->name,
            'order' => CompanyDivision::where('company_id', $company->id)->max('order') + 1,
        ]);

        return response()->json(['success' => true, 'division' => $division]);
    }

    public function update(Request $request, $id)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $company = $request->user()->company ?? Company::first();

        $division = CompanyDivision::where('company_id', $company->id)->findOrFail($id);
        $division->update(['name' => $request->name]);

        Cache::forget("company.{$company->id}.features.catalogue");

        return response()->json(['success' => true]);
    }

    public function assignFeature(Request $request)
    {
        $request->validate([
            'feature_key' => 'required|string',
            'division_id' => 'nullable|integer',
        ]);

        $company = $request->user()->company ?? Company::first();

        // If assigning to "Uncategorized" (null division_id)
        $divisionId = $request->division_id;
        if ($divisionId) {
            // Verify division belongs to company
            CompanyDivision::where('company_id', $company->id)->findOrFail($divisionId);
        }

        CompanyFeature::updateOrCreate(
            ['company_id' => $company->id, 'feature_key' => $request->feature_key],
            ['company_division_id' => $divisionId]
        );

        Cache::forget("company.{$company->id}.features.catalogue");

        return response()->json(['success' => true]);
    }

    public function destroy(Request $request, $id)
    {
        $company = $request->user()->company ?? Company::first();
        $division = CompanyDivision::where('company_id', $company->id)->findOrFail($id);

        // Move all features in this division to uncategorized
        CompanyFeature::where('company_division_id', $division->id)
            ->update(['company_division_id' => null]);

        $division->delete();

        Cache::forget("company.{$company->id}.features.catalogue");

        return response()->json(['success' => true]);
    }

    public function destroyByName(Request $request)
    {
        $request->validate(['name' => 'required|string']);
        $company = $request->user()->company ?? Company::first();

        $division = CompanyDivision::where('company_id', $company->id)
            ->where('name', $request->name)
            ->first();

        if (! $division) {
            return response()->json(['success' => false, 'message' => 'Divisi tidak ditemukan di pengaturan.']);
        }

        CompanyFeature::where('company_division_id', $division->id)
            ->update(['company_division_id' => null]);

        $division->delete();

        Cache::forget("company.{$company->id}.features.catalogue");

        return response()->json(['success' => true]);
    }
}
