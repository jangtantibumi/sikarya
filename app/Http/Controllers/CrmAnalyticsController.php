<?php

namespace App\Http\Controllers;

use App\Services\CrmAnalyticsService;
use Illuminate\Http\Request;

class CrmAnalyticsController extends Controller
{
    protected $analyticsService;

    public function __construct(CrmAnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    public function index(Request $request)
    {
        $data = $this->analyticsService->getAnalyticsOverview();

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'data' => $data,
            ]);
        }

        return view('crm.analytics.index', $data);
    }
}
