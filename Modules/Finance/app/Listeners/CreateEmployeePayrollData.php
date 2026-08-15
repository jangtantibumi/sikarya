<?php

declare(strict_types=1);

namespace Modules\Finance\Listeners;

use App\Events\HRIS\EmployeeOnboarded;
use Illuminate\Contracts\Queue\ShouldQueue;

class CreateEmployeePayrollData implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(EmployeeOnboarded $event): void
    {
        // Finance module reacts to HRIS event asynchronously or synchronously.
        // E.g.
        // Payroll::create(['employee_id' => $event->employeeId, 'status' => 'pending_setup']);
        \Log::info("Finance Module received EmployeeOnboarded event for Employee ID: {$event->employeeId} ({$event->name}). Payroll setup initiated.");
    }
}
