<?php

declare(strict_types=1);

namespace Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Finance\Http\Requests\StoreExchangeRateRequest;
use Modules\Finance\Http\Requests\UpdateExchangeRateRequest;
use Modules\Finance\Services\CurrencyService;
use Modules\Finance\Services\ExchangeRateService;

class ExchangeRateController extends Controller
{
    public function __construct(
        protected ExchangeRateService $service,
        protected CurrencyService $currencyService
    ) {}

    public function index()
    {
        $rates = $this->service->getAllRates();
        $currencies = $this->currencyService->getAllCurrencies();

        if (request()->wantsJson()) {
            return response()->json(['data' => $rates]);
        }

        return view('finance::exchange-rates.index', compact('rates', 'currencies'));
    }

    public function store(StoreExchangeRateRequest $request)
    {
        $rate = $this->service->createRate($request->validated());

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Exchange Rate created successfully', 'data' => $rate], 201);
        }

        return redirect()->back()->with('success', 'Exchange Rate created successfully.');
    }

    public function show(string $id): JsonResponse
    {
        $rate = $this->service->getRateById($id);
        if (! $rate) {
            return response()->json(['message' => 'Exchange Rate not found'], 404);
        }

        return response()->json(['data' => $rate]);
    }

    public function update(UpdateExchangeRateRequest $request, string $id)
    {
        $updated = $this->service->updateRate($id, $request->validated());

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Exchange Rate updated successfully', 'success' => $updated]);
        }

        return redirect()->back()->with('success', 'Exchange Rate updated successfully.');
    }

    public function destroy(string $id)
    {
        $deleted = $this->service->deleteRate($id);

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Exchange Rate deleted successfully', 'success' => $deleted]);
        }

        return redirect()->back()->with('success', 'Exchange Rate deleted successfully.');
    }
}
