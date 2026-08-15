<?php

namespace Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Finance\Http\Requests\StoreProfitCenterRequest;
use Modules\Finance\Http\Requests\UpdateProfitCenterRequest;
use Modules\Finance\Services\ProfitCenterService;

class ProfitCenterController extends Controller
{
    public function __construct(
        protected ProfitCenterService $service
    ) {}

    public function index()
    {
        $profitCenters = $this->service->getAllProfitCenters();

        if (request()->wantsJson()) {
            return response()->json(['data' => $profitCenters]);
        }

        return view('finance::profit-centers.index', compact('profitCenters'));
    }

    public function store(StoreProfitCenterRequest $request)
    {
        $profitCenter = $this->service->createProfitCenter($request->validated());

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Profit Center created successfully', 'data' => $profitCenter], 201);
        }

        return redirect()->back()->with('success', 'Profit Center created successfully.');
    }

    public function show(string $id): JsonResponse
    {
        $profitCenter = $this->service->getProfitCenterById($id);
        if (! $profitCenter) {
            return response()->json(['message' => 'Profit Center not found'], 404);
        }

        return response()->json(['data' => $profitCenter]);
    }

    public function update(UpdateProfitCenterRequest $request, string $id)
    {
        $updated = $this->service->updateProfitCenter($id, $request->validated());

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Profit Center updated successfully', 'success' => $updated]);
        }

        return redirect()->back()->with('success', 'Profit Center updated successfully.');
    }

    public function destroy(string $id)
    {
        $deleted = $this->service->deleteProfitCenter($id);

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Profit Center deleted successfully', 'success' => $deleted]);
        }

        return redirect()->back()->with('success', 'Profit Center deleted successfully.');
    }
}
