<?php

declare(strict_types=1);

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

Route::apiResource('account-groups', AccountGroupController::class);
Route::apiResource('chart-of-accounts', ChartOfAccountController::class);
Route::apiResource('currencies', CurrencyController::class);
Route::apiResource('fiscal-years', FiscalYearController::class);
Route::apiResource('fiscal-periods', FiscalPeriodController::class);
Route::apiResource('exchange-rates', ExchangeRateController::class);
Route::apiResource('cost-centers', CostCenterController::class);
Route::apiResource('profit-centers', ProfitCenterController::class);
Route::apiResource('tax-masters', TaxMasterController::class);
Route::apiResource('payment-terms', PaymentTermController::class);
Route::apiResource('numbering-series', NumberingSeriesController::class);
