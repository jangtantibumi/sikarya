<?php

declare(strict_types=1);

namespace Modules\Finance\Policies;

use App\Models\User;
use Modules\Finance\Models\Currency;

class CurrencyPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Currency $currency): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Currency $currency): bool
    {
        return true;
    }

    public function delete(User $user, Currency $currency): bool
    {
        return true;
    }
}
