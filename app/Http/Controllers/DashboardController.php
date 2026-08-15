<?php

namespace App\Http\Controllers;

use App\Services\FeatureManager;

class DashboardController extends Controller
{
    public function __construct(private readonly FeatureManager $features)
    {
    }

    public function index()
    {
        return view('dashboard', [
            'featureFlags' => $this->features->states(),
        ]);
    }
}
