<?php

declare(strict_types=1);

namespace Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Finance\Http\Requests\StoreFiscalYearRequest;
use Modules\Finance\Http\Requests\UpdateFiscalYearRequest;
use Modules\Finance\Services\FiscalYearService;

class FiscalYearController extends Controller
{
    public function __construct(
        protected FiscalYearService $service
    ) {}

    public function index()
    {
        $fiscalYears = $this->service->getAllFiscalYears();

        if (request()->wantsJson()) {
            return response()->json(['data' => $fiscalYears]);
        }

        return view('finance::fiscal-years.index', compact('fiscalYears'));
    }

    public function store(StoreFiscalYearRequest $request)
    {
        $fiscalYear = $this->service->createFiscalYearWithPeriods($request->validated());

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Fiscal Year and 12 periods created successfully', 'data' => $fiscalYear], 201);
        }

        return redirect()->back()->with('success', 'Fiscal Year and periods created successfully.');
    }

    public function show(string $id): JsonResponse
    {
        $fiscalYear = $this->service->getFiscalYearById($id);
        if (! $fiscalYear) {
            return response()->json(['message' => 'Fiscal Year not found'], 404);
        }

        return response()->json(['data' => $fiscalYear]);
    }

    public function update(UpdateFiscalYearRequest $request, string $id)
    {
        $updated = $this->service->updateFiscalYear($id, $request->validated());

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Fiscal Year updated successfully', 'success' => $updated]);
        }

        return redirect()->back()->with('success', 'Fiscal Year updated successfully.');
    }

    public function destroy(string $id)
    {
        $deleted = $this->service->deleteFiscalYear($id);

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Fiscal Year deleted successfully', 'success' => $deleted]);
        }

        return redirect()->back()->with('success', 'Fiscal Year deleted successfully.');
    }
}
