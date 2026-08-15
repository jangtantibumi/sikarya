<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Services\CompanyFeatureManager;
use App\Services\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CompanyModuleController extends Controller
{
    public function __construct(
        private readonly CompanyFeatureManager $features,
        private readonly TenantContext $tenant,
    ) {
    }

    public function show(Request $request): JsonResponse
    {
        $company = $this->companyFor($request);
        abort_unless($request->user()->can('view', $company), 403);

        return response()->json([
            'company' => [
                'id' => $company->id,
                'name' => $company->name,
                'industry' => $company->industry,
                'timezone' => $company->timezone,
                'currency' => $company->currency,
            ],
            'can_manage_modules' => $request->user()->can('manageModules', $company),
            'features' => $this->features->catalogue($company),
        ]);
    }

    public function update(Request $request, string $feature): JsonResponse
    {
        $company = $this->companyFor($request);
        abort_unless($request->user()->can('manageModules', $company), 403);
        $validated = $request->validate(['state' => ['required', 'in:active,read_only,off']]);

        try {
            $updated = $this->features->set($company, $feature, $validated['state']);
        } catch (ValidationException $exception) {
            throw $exception;
        }

        return response()->json([
            'success' => true,
            'message' => "Status modul {$updated->feature_key} diperbarui.",
            'feature' => $updated,
            'features' => $this->features->catalogue($company),
        ]);
    }

    private function companyFor(Request $request): Company
    {
        abort_unless($this->tenant->id(), 422, 'Akun ini belum dipetakan ke perusahaan.');

        return Company::query()->findOrFail($this->tenant->id());
    }
}
