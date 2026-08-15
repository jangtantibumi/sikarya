<?php

declare(strict_types=1);

namespace Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Finance\Http\Requests\StorePaymentTermRequest;
use Modules\Finance\Http\Requests\UpdatePaymentTermRequest;
use Modules\Finance\Services\PaymentTermService;

class PaymentTermController extends Controller
{
    public function __construct(
        protected PaymentTermService $service
    ) {}

    public function index()
    {
        $terms = $this->service->getAllPaymentTerms();

        if (request()->wantsJson()) {
            return response()->json(['data' => $terms]);
        }

        return view('finance::payment-terms.index', compact('terms'));
    }

    public function store(StorePaymentTermRequest $request)
    {
        $term = $this->service->createPaymentTerm($request->validated());

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Payment Term created successfully', 'data' => $term], 201);
        }

        return redirect()->back()->with('success', 'Payment Term created successfully.');
    }

    public function show(string $id): JsonResponse
    {
        $term = $this->service->getPaymentTermById($id);
        if (! $term) {
            return response()->json(['message' => 'Payment Term not found'], 404);
        }

        return response()->json(['data' => $term]);
    }

    public function update(UpdatePaymentTermRequest $request, string $id)
    {
        $updated = $this->service->updatePaymentTerm($id, $request->validated());

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Payment Term updated successfully', 'success' => $updated]);
        }

        return redirect()->back()->with('success', 'Payment Term updated successfully.');
    }

    public function destroy(string $id)
    {
        $deleted = $this->service->deletePaymentTerm($id);

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Payment Term deleted successfully', 'success' => $deleted]);
        }

        return redirect()->back()->with('success', 'Payment Term deleted successfully.');
    }
}
