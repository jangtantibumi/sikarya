<?php

declare(strict_types=1);

namespace Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Finance\Http\Requests\StoreCostCenterRequest;
use Modules\Finance\Http\Requests\UpdateCostCenterRequest;
use Modules\Finance\Services\CostCenterService;

class CostCenterController extends Controller
{
    public function __construct(
        protected CostCenterService $service
    ) {}

    public function index()
    {
        $costCenters = $this->service->getAllCostCenters();

        if (request()->wantsJson()) {
            return response()->json(['data' => $costCenters]);
        }

        return view('finance::cost-centers.index', compact('costCenters'));
    }

    public function store(StoreCostCenterRequest $request)
    {
        $costCenter = $this->service->createCostCenter($request->validated());

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Cost Center created successfully', 'data' => $costCenter], 201);
        }

        return redirect()->back()->with('success', 'Cost Center created successfully.');
    }

    public function show(string $id): JsonResponse
    {
        $costCenter = $this->service->getCostCenterById($id);
        if (! $costCenter) {
            return response()->json(['message' => 'Cost Center not found'], 404);
        }

        return response()->json(['data' => $costCenter]);
    }

    public function update(UpdateCostCenterRequest $request, string $id)
    {
        $updated = $this->service->updateCostCenter($id, $request->validated());

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Cost Center updated successfully', 'success' => $updated]);
        }

        return redirect()->back()->with('success', 'Cost Center updated successfully.');
    }

    public function destroy(string $id)
    {
        $deleted = $this->service->deleteCostCenter($id);

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Cost Center deleted successfully', 'success' => $deleted]);
        }

        return redirect()->back()->with('success', 'Cost Center deleted successfully.');
    }
}
