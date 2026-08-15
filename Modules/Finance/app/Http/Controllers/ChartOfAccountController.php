<?php

namespace Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Finance\Http\Requests\StoreChartOfAccountRequest;
use Modules\Finance\Http\Requests\UpdateChartOfAccountRequest;
use Modules\Finance\Services\AccountGroupService;
use Modules\Finance\Services\ChartOfAccountService;
use Modules\Finance\Services\CurrencyService;

class ChartOfAccountController extends Controller
{
    public function __construct(
        protected ChartOfAccountService $coaService,
        protected AccountGroupService $accountGroupService,
        protected CurrencyService $currencyService
    ) {}

    public function index()
    {
        $accounts = $this->coaService->getAllAccounts();
        $accountGroups = $this->accountGroupService->getAllAccountGroups();
        $currencies = $this->currencyService->getAllCurrencies();

        if (request()->wantsJson()) {
            return response()->json(['data' => $accounts]);
        }

        return view('finance::chart-of-accounts.index', compact('accounts', 'accountGroups', 'currencies'));
    }

    public function store(StoreChartOfAccountRequest $request)
    {
        $account = $this->coaService->createAccount($request->validated());

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Account created successfully', 'data' => $account], 201);
        }

        return redirect()->back()->with('success', 'Chart of Account successfully created.');
    }

    public function show(string $id): JsonResponse
    {
        $account = $this->coaService->getAccountById($id);
        if (! $account) {
            return response()->json(['message' => 'Account not found'], 404);
        }

        return response()->json(['data' => $account]);
    }

    public function update(UpdateChartOfAccountRequest $request, string $id)
    {
        $updated = $this->coaService->updateAccount($id, $request->validated());

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Account updated successfully', 'success' => $updated]);
        }

        return redirect()->back()->with('success', 'Chart of Account updated successfully.');
    }

    public function destroy(string $id)
    {
        $deleted = $this->coaService->deleteAccount($id);

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Account deleted successfully', 'success' => $deleted]);
        }

        return redirect()->back()->with('success', 'Chart of Account deleted successfully.');
    }
}
