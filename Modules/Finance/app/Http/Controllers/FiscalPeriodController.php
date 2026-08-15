<?php

declare(strict_types=1);

namespace Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Finance\Http\Requests\StoreFiscalPeriodRequest;
use Modules\Finance\Http\Requests\UpdateFiscalPeriodRequest;
use Modules\Finance\Models\FiscalPeriod;

class FiscalPeriodController extends Controller
{
    /**
     * Display periods for a given fiscal year.
     */
    public function index(?string $fiscalYearId = null)
    {
        $query = FiscalPeriod::orderBy('period_number');

        if ($fiscalYearId) {
            $query->where('fiscal_year_id', $fiscalYearId);
        }

        $periods = $query->get();

        if (request()->wantsJson()) {
            return response()->json(['data' => $periods]);
        }

        return view('finance::fiscal-periods.index', compact('periods'));
    }

    /**
     * Store a new fiscal period (manual override).
     */
    public function store(StoreFiscalPeriodRequest $request): JsonResponse
    {
        $period = FiscalPeriod::create($request->validated());

        return response()->json([
            'message' => 'Fiscal Period created successfully',
            'data' => $period,
        ], 201);
    }

    /**
     * Show a single period.
     */
    public function show(string $id): JsonResponse
    {
        $period = FiscalPeriod::findOrFail($id);

        return response()->json(['data' => $period]);
    }

    /**
     * Update period status (open / close / lock).
     */
    public function update(UpdateFiscalPeriodRequest $request, string $id): JsonResponse
    {
        $period = FiscalPeriod::findOrFail($id);
        $period->update($request->validated());

        return response()->json([
            'message' => 'Fiscal Period updated successfully',
            'data' => $period,
        ]);
    }

    /**
     * Delete a period.
     */
    public function destroy(string $id): JsonResponse
    {
        $period = FiscalPeriod::findOrFail($id);
        $period->delete();

        return response()->json(['message' => 'Fiscal Period deleted successfully']);
    }
}
