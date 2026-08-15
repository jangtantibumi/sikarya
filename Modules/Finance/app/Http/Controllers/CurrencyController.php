<?php

declare(strict_types=1);

namespace Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Finance\Http\Requests\StoreCurrencyRequest;
use Modules\Finance\Http\Requests\UpdateCurrencyRequest;
use Modules\Finance\Services\CurrencyService;

class CurrencyController extends Controller
{
    public function __construct(
        protected CurrencyService $service
    ) {}

    public function index()
    {
        $currencies = $this->service->getAllCurrencies();

        if (request()->wantsJson()) {
            return response()->json(['data' => $currencies]);
        }

        return view('finance::currencies.index', compact('currencies'));
    }

    public function store(StoreCurrencyRequest $request)
    {
        $currency = $this->service->createCurrency($request->validated());

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Currency created successfully', 'data' => $currency], 201);
        }

        return redirect()->back()->with('success', 'Currency created successfully.');
    }

    public function show(string $id): JsonResponse
    {
        $currency = $this->service->getCurrencyById($id);
        if (! $currency) {
            return response()->json(['message' => 'Currency not found'], 404);
        }

        return response()->json(['data' => $currency]);
    }

    public function update(UpdateCurrencyRequest $request, string $id)
    {
        $updated = $this->service->updateCurrency($id, $request->validated());

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Currency updated successfully', 'success' => $updated]);
        }

        return redirect()->back()->with('success', 'Currency updated successfully.');
    }

    public function destroy(string $id)
    {
        $deleted = $this->service->deleteCurrency($id);

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Currency deleted successfully', 'success' => $deleted]);
        }

        return redirect()->back()->with('success', 'Currency deleted successfully.');
    }
}
