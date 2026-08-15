<?php

declare(strict_types=1);

namespace App\Events\HRIS;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EmployeeOnboarded
{
    use Dispatchable, SerializesModels;

    public int $employeeId;
    public string $name;
    public string $department;

    public function __construct(int $employeeId, string $name, string $department)
    {
        $this->employeeId = $employeeId;
        $this->name = $name;
        $this->department = $department;
    }
}
