<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;

class ChatChannelService
{
    public const CHANNEL_ROLES = [
        'general' => ['*'],
        'marketing-team' => ['ceo', 'mgr_marketing', 'staff_marketing'],
        'operations-team' => ['ceo', 'mgr_ops', 'staff_ops'],
        'finance-team' => ['ceo', 'mgr_finance', 'staff_finance'],
        'hr-team' => ['ceo', 'mgr_hrd', 'staff_hrd', 'hrd', 'hrd_manager', 'hr'],
        'management' => ['ceo', 'mgr_marketing', 'mgr_ops', 'mgr_finance', 'mgr_hrd'],
    ];

    public function allowed(User $user): array
    {
        if ($user->isAlumni()) {
            return [];
        }

        return collect(self::CHANNEL_ROLES)
            ->filter(fn (array $roles) => in_array('*', $roles, true) || in_array($user->role, $roles, true))
            ->keys()
            ->values()
            ->all();
    }

    public function canAccess(User $user, string $channel): bool
    {
        return in_array($channel, $this->allowed($user), true);
    }
}
