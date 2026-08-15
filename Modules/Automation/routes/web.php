<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Automation\Http\Controllers\AutomationController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('automations', AutomationController::class)->names('automation');
});
