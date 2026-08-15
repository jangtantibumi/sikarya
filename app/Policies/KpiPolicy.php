<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\KpiPlan;
use App\Models\User;

class KpiPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, KpiPlan $kpiPlan): bool
    {
        if ($user->isCEO()) {
            return true;
        }

        return $kpiPlan->divisionKey() === $user->divisionKey()
            && ($kpiPlan->status === 'approved' || $kpiPlan->manager_id === $user->id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isManager();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, KpiPlan $kpiPlan): bool
    {
        return $user->id === $kpiPlan->manager_id
            && in_array($kpiPlan->status, ['pending_ceo', 'rejected'], true);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, KpiPlan $kpiPlan): bool
    {
        return $user->id === $kpiPlan->manager_id && $kpiPlan->status === 'pending_ceo';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, KpiPlan $kpiPlan): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, KpiPlan $kpiPlan): bool
    {
        return false;
    }
}
