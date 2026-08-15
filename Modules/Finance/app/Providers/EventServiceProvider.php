<?php

declare(strict_types=1);

namespace Modules\Finance\Providers;

use App\Events\HRIS\EmployeeOnboarded;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Finance\Listeners\CreateEmployeePayrollData;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        EmployeeOnboarded::class => [
            CreateEmployeePayrollData::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }
}
