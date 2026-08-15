<?php

namespace Modules\Finance\Policies;

use App\Models\User;
use Modules\Finance\Models\FiscalYear;

class FiscalYearPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, FiscalYear $year): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, FiscalYear $year): bool
    {
        return true;
    }

    public function delete(User $user, FiscalYear $year): bool
    {
        return true;
    }
}
