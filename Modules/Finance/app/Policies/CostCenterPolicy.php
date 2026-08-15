<?php

declare(strict_types=1);

namespace Modules\Finance\Policies;

use App\Models\User;
use Modules\Finance\Models\CostCenter;

class CostCenterPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, CostCenter $cc): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, CostCenter $cc): bool
    {
        return true;
    }

    public function delete(User $user, CostCenter $cc): bool
    {
        return true;
    }
}
