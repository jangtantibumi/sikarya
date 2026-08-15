<?php

use Illuminate\Support\Facades\Route;
use Modules\MasterData\Http\Controllers\MasterDataController;

Route::prefix('master-demo')->middleware(['master.demo.auth'])->group(function () {
    Route::resource('masterdatas', MasterDataController::class)->names('masterdata');
});
