<?php

declare(strict_types=1);

namespace Modules\Finance\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Finance\Repositories\Contracts\AccountGroupRepositoryInterface;
use Modules\Finance\Repositories\Contracts\ChartOfAccountRepositoryInterface;
use Modules\Finance\Repositories\Contracts\CostCenterRepositoryInterface;
use Modules\Finance\Repositories\Contracts\CurrencyRepositoryInterface;
use Modules\Finance\Repositories\Contracts\ExchangeRateRepositoryInterface;
use Modules\Finance\Repositories\Contracts\FiscalYearRepositoryInterface;
use Modules\Finance\Repositories\Contracts\NumberingSeriesRepositoryInterface;
use Modules\Finance\Repositories\Contracts\PaymentTermRepositoryInterface;
use Modules\Finance\Repositories\Contracts\ProfitCenterRepositoryInterface;
use Modules\Finance\Repositories\Contracts\TaxMasterRepositoryInterface;
use Modules\Finance\Repositories\Eloquent\AccountGroupRepository;
use Modules\Finance\Repositories\Eloquent\ChartOfAccountRepository;
use Modules\Finance\Repositories\Eloquent\CostCenterRepository;
use Modules\Finance\Repositories\Eloquent\CurrencyRepository;
use Modules\Finance\Repositories\Eloquent\ExchangeRateRepository;
use Modules\Finance\Repositories\Eloquent\FiscalYearRepository;
use Modules\Finance\Repositories\Eloquent\NumberingSeriesRepository;
use Modules\Finance\Repositories\Eloquent\PaymentTermRepository;
use Modules\Finance\Repositories\Eloquent\ProfitCenterRepository;
use Modules\Finance\Repositories\Eloquent\TaxMasterRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AccountGroupRepositoryInterface::class, AccountGroupRepository::class);
        $this->app->bind(ChartOfAccountRepositoryInterface::class, ChartOfAccountRepository::class);
        $this->app->bind(CurrencyRepositoryInterface::class, CurrencyRepository::class);
        $this->app->bind(FiscalYearRepositoryInterface::class, FiscalYearRepository::class);
        $this->app->bind(ExchangeRateRepositoryInterface::class, ExchangeRateRepository::class);
        $this->app->bind(CostCenterRepositoryInterface::class, CostCenterRepository::class);
        $this->app->bind(ProfitCenterRepositoryInterface::class, ProfitCenterRepository::class);
        $this->app->bind(TaxMasterRepositoryInterface::class, TaxMasterRepository::class);
        $this->app->bind(PaymentTermRepositoryInterface::class, PaymentTermRepository::class);
        $this->app->bind(NumberingSeriesRepositoryInterface::class, NumberingSeriesRepository::class);
    }
}
