<?php

declare(strict_types=1);

namespace App\Actions\HRIS;

use App\Events\HRIS\EmployeeOnboarded;
use Illuminate\Support\Facades\DB;

class RegisterNewEmployeeAction
{
    /**
     * Execute the action.
     * 
     * @param array $employeeData
     * @return object Employee mock object
     */
    public function execute(array $employeeData): object
    {
        return DB::transaction(function () use ($employeeData) {
            // 1. Insert into Database
            // $employee = Employee::create($employeeData);
            
            // Mock employee object for demonstration
            $employee = (object) [
                'id' => rand(1000, 9999),
                'name' => $employeeData['name'],
                'department' => $employeeData['department'],
            ];

            \Log::info("HRIS: Employee registered successfully: {$employee->name}");

            // 2. Dispatch the event so other modules (like Finance) can react
            EmployeeOnboarded::dispatch($employee->id, $employee->name, $employee->department);

            return $employee;
        });
    }
}
