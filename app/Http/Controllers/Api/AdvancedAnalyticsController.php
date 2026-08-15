<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AdvancedAnalyticsService;
use Illuminate\Http\Request;

class AdvancedAnalyticsController extends Controller
{
    public function __construct(private readonly AdvancedAnalyticsService $analytics) {}

    public function index(Request $request)
    {
        $validated = $request->validate([
            'year' => ['nullable', 'integer', 'between:2020,2100'],
        ]);
        $year = (int) ($validated['year'] ?? now()->year);

        return response()->json(
            $this->analytics->overview($request->user(), $year),
        );
    }
}
