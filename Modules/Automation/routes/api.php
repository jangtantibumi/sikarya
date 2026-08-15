<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Automation\Http\Controllers\AutomationController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('automations', AutomationController::class)->names('automation');
});
