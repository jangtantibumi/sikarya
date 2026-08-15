<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ApprovalRequest;
use App\Models\User;

class ApprovalPolicy
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
    public function view(User $user, ApprovalRequest $approvalRequest): bool
    {
        if ($user->isCEO()) {
            return true;
        }
        if ($user->id === $approvalRequest->requester_id) {
            return true;
        }
        if ($user->id === $approvalRequest->current_approver_id) {
            return true;
        }
        if ($user->isHRD() && in_array($approvalRequest->request_type, ['leave', 'team_request', 'resignation'])) {
            return true;
        }

        // Managers can view if requester is their staff
        if ($user->isManager() && $user->isManagerOf($approvalRequest->requester)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ApprovalRequest $approvalRequest): bool
    {
        if ($approvalRequest->status === 'pending_manager') {
            return $user->isManager() && $user->id === $approvalRequest->current_approver_id;
        }

        if ($approvalRequest->status === 'pending_ceo') {
            return $user->isCEO();
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ApprovalRequest $approvalRequest): bool
    {
        // Only requester can cancel their request if it is still pending
        return $user->id === $approvalRequest->requester_id && in_array($approvalRequest->status, ['draft', 'pending_manager', 'pending_ceo']);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ApprovalRequest $approvalRequest): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ApprovalRequest $approvalRequest): bool
    {
        return false;
    }
}
