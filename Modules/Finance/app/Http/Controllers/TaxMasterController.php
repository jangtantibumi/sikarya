<?php

declare(strict_types=1);

namespace Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Finance\Http\Requests\StoreTaxMasterRequest;
use Modules\Finance\Http\Requests\UpdateTaxMasterRequest;
use Modules\Finance\Services\ChartOfAccountService;
use Modules\Finance\Services\TaxMasterService;

class TaxMasterController extends Controller
{
    public function __construct(
        protected TaxMasterService $service,
        protected ChartOfAccountService $coaService
    ) {}

    public function index()
    {
        $taxes = $this->service->getAllTaxes();
        $accounts = $this->coaService->getAllAccounts();

        if (request()->wantsJson()) {
            return response()->json(['data' => $taxes]);
        }

        return view('finance::tax-masters.index', compact('taxes', 'accounts'));
    }

    public function store(StoreTaxMasterRequest $request)
    {
        $tax = $this->service->createTax($request->validated());

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Tax Master created successfully', 'data' => $tax], 201);
        }

        return redirect()->back()->with('success', 'Tax Master created successfully.');
    }

    public function show(string $id): JsonResponse
    {
        $tax = $this->service->getTaxById($id);
        if (! $tax) {
            return response()->json(['message' => 'Tax Master not found'], 404);
        }

        return response()->json(['data' => $tax]);
    }

    public function update(UpdateTaxMasterRequest $request, string $id)
    {
        $updated = $this->service->updateTax($id, $request->validated());

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Tax Master updated successfully', 'success' => $updated]);
        }

        return redirect()->back()->with('success', 'Tax Master updated successfully.');
    }

    public function destroy(string $id)
    {
        $deleted = $this->service->deleteTax($id);

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Tax Master deleted successfully', 'success' => $deleted]);
        }

        return redirect()->back()->with('success', 'Tax Master deleted successfully.');
    }
}
