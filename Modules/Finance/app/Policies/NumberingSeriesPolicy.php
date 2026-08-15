<?php

declare(strict_types=1);

namespace Modules\Finance\Policies;

use App\Models\User;
use Modules\Finance\Models\NumberingSeries;

class NumberingSeriesPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, NumberingSeries $series): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, NumberingSeries $series): bool
    {
        return true;
    }

    public function delete(User $user, NumberingSeries $series): bool
    {
        return true;
    }
}
