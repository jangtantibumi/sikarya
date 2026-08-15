<?php

declare(strict_types=1);

namespace Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Finance\Http\Requests\StoreAccountGroupRequest;
use Modules\Finance\Http\Requests\UpdateAccountGroupRequest;
use Modules\Finance\Services\AccountGroupService;

class AccountGroupController extends Controller
{
    public function __construct(
        protected AccountGroupService $service
    ) {}

    public function index()
    {
        $groups = $this->service->getAllAccountGroups();

        if (request()->wantsJson()) {
            return response()->json(['data' => $groups]);
        }

        return view('finance::account-groups.index', compact('groups'));
    }

    public function store(StoreAccountGroupRequest $request)
    {
        $group = $this->service->createAccountGroup($request->validated());

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Account Group created successfully', 'data' => $group], 201);
        }

        return redirect()->back()->with('success', 'Account Group created successfully.');
    }

    public function show(string $id): JsonResponse
    {
        $group = $this->service->getAccountGroupById($id);
        if (! $group) {
            return response()->json(['message' => 'Account Group not found'], 404);
        }

        return response()->json(['data' => $group]);
    }

    public function update(UpdateAccountGroupRequest $request, string $id)
    {
        $updated = $this->service->updateAccountGroup($id, $request->validated());

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Account Group updated successfully', 'success' => $updated]);
        }

        return redirect()->back()->with('success', 'Account Group updated successfully.');
    }

    public function destroy(string $id)
    {
        $deleted = $this->service->deleteAccountGroup($id);

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Account Group deleted successfully', 'success' => $deleted]);
        }

        return redirect()->back()->with('success', 'Account Group deleted successfully.');
    }
}
