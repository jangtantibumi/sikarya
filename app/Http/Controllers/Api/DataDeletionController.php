<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DataDeletionRequest;
use App\Services\DataDeletionRequestService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DataDeletionController extends Controller
{
    public function __construct(
        private readonly DataDeletionRequestService $deletions,
    ) {
    }

    public function index(Request $request)
    {
        $viewer = $request->user();
        $query = DataDeletionRequest::query()->with([
            'requester:id,name,username,role',
            'executor:id,name,username,role',
            'approvalRequest.steps.approver:id,name,username,role',
        ]);

        if (!$viewer->isCEO()) {
            if ($viewer->isManager()) {
                $query->where(function (Builder $builder) use ($viewer): void {
                    $builder
                        ->where('requested_by_id', $viewer->id)
                        ->orWhereHas('requester', fn (Builder $users) => $users->where('parent', $viewer->username));
                });
            } else {
                $query->where('requested_by_id', $viewer->id);
            }
        }

        return response()->json([
            'requests' => $query->latest('id')->limit(200)->get(),
            'supported_types' => $this->deletions->supportedTypes(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'resource_type' => [
                'required',
                'string',
                Rule::in($this->deletions->supportedTypes()),
            ],
            'resource_id' => ['required', 'integer', 'min:1'],
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        $result = $this->deletions->request(
            $request->user(),
            $validated['resource_type'],
            (int) $validated['resource_id'],
            $validated['reason'],
        );

        return response()->json([
            'success' => true,
            ...$result,
        ], $result['approval'] ? 202 : 200);
    }
}
