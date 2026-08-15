<?php

declare(strict_types=1);

namespace Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Finance\Http\Requests\StoreNumberingSeriesRequest;
use Modules\Finance\Http\Requests\UpdateNumberingSeriesRequest;
use Modules\Finance\Services\NumberingSeriesService;

class NumberingSeriesController extends Controller
{
    public function __construct(
        protected NumberingSeriesService $service
    ) {}

    public function index()
    {
        $series = $this->service->getAllSeries();

        if (request()->wantsJson()) {
            return response()->json(['data' => $series]);
        }

        return view('finance::numbering-series.index', compact('series'));
    }

    public function store(StoreNumberingSeriesRequest $request)
    {
        $ns = $this->service->createSeries($request->validated());

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Numbering Series created successfully', 'data' => $ns], 201);
        }

        return redirect()->back()->with('success', 'Numbering Series created successfully.');
    }

    public function show(string $id): JsonResponse
    {
        $ns = $this->service->getSeriesById($id);
        if (! $ns) {
            return response()->json(['message' => 'Numbering Series not found'], 404);
        }

        return response()->json(['data' => $ns]);
    }

    public function update(UpdateNumberingSeriesRequest $request, string $id)
    {
        $updated = $this->service->updateSeries($id, $request->validated());

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Numbering Series updated successfully', 'success' => $updated]);
        }

        return redirect()->back()->with('success', 'Numbering Series updated successfully.');
    }

    public function destroy(string $id)
    {
        $deleted = $this->service->deleteSeries($id);

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Numbering Series deleted successfully', 'success' => $deleted]);
        }

        return redirect()->back()->with('success', 'Numbering Series deleted successfully.');
    }
}
