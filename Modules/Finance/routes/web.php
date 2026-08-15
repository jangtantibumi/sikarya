<?php

use Illuminate\Support\Facades\Route;
use Modules\Finance\Http\Controllers\AccountGroupController;
use Modules\Finance\Http\Controllers\ChartOfAccountController;
use Modules\Finance\Http\Controllers\CostCenterController;
use Modules\Finance\Http\Controllers\CurrencyController;
use Modules\Finance\Http\Controllers\ExchangeRateController;
use Modules\Finance\Http\Controllers\FiscalPeriodController;
use Modules\Finance\Http\Controllers\FiscalYearController;
use Modules\Finance\Http\Controllers\NumberingSeriesController;
use Modules\Finance\Http\Controllers\PaymentTermController;
use Modules\Finance\Http\Controllers\ProfitCenterController;
use Modules\Finance\Http\Controllers\TaxMasterController;

Route::prefix('finance')->name('finance.')->middleware(['web', 'auth', 'tenant.context'])->group(function () {
    Route::resource('account-groups', AccountGroupController::class);
    Route::resource('chart-of-accounts', ChartOfAccountController::class);
    Route::resource('currencies', CurrencyController::class);
    Route::resource('fiscal-years', FiscalYearController::class);
    Route::resource('fiscal-periods', FiscalPeriodController::class);
    Route::resource('exchange-rates', ExchangeRateController::class);
    Route::resource('cost-centers', CostCenterController::class);
    Route::resource('profit-centers', ProfitCenterController::class);
    Route::resource('tax-masters', TaxMasterController::class);
    Route::resource('payment-terms', PaymentTermController::class);
    Route::resource('numbering-series', NumberingSeriesController::class);
});
