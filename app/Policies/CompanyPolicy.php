<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Company;
use App\Models\User;

class CompanyPolicy
{
    public function view(User $user, Company $company): bool
    {
        return $user->belongsToCompany($company);
    }

    public function manageModules(User $user, Company $company): bool
    {
        if ($user->isPlatformAdmin()) {
            return true;
        }

        if (! $user->isCEO() || ! $user->belongsToCompany($company)) {
            return false;
        }

        $membership = $user->companyMemberships()
            ->where('company_id', $company->id)
            ->where('is_active', true)
            ->first();

        return $membership === null || $membership->is_owner;
    }
}
