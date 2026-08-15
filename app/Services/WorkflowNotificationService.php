<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\WorkflowStatusUpdated;
use Illuminate\Support\Collection;

class WorkflowNotificationService
{
    public function send(
        User|Collection|array $recipients,
        string $title,
        string $message,
        string $key,
        string $category = 'workflow',
        ?string $actionUrl = null,
        array $meta = [],
    ): void {
        $users = $recipients instanceof User
            ? collect([$recipients])
            : collect($recipients);

        $users
            ->filter(fn ($user) => $user instanceof User && $user->is_active)
            ->unique('id')
            ->each(function (User $user) use ($title, $message, $key, $category, $actionUrl, $meta): void {
                if ($this->exists($user, $key)) {
                    return;
                }

                $user->notify(new WorkflowStatusUpdated(
                    message: $message,
                    idempotencyKey: $key,
                    title: $title,
                    category: $category,
                    actionUrl: $actionUrl,
                    meta: $meta,
                ));
            });
    }

    public function ceos(): Collection
    {
        return User::query()->where('role', 'ceo')->where('is_active', true)->get();
    }

    public function hrdUsers(): Collection
    {
        return User::query()
            ->whereIn('role', ['mgr_hrd', 'staff_hrd', 'hrd', 'hrd_manager', 'hr'])
            ->where('is_active', true)
            ->get();
    }

    public function managersForDivision(?string $division): Collection
    {
        if (!$division) {
            return collect();
        }

        return User::query()
            ->where('role', 'like', 'mgr\\_%')
            ->where('is_active', true)
            ->get()
            ->filter(fn (User $user) => $user->divisionKey() === $division)
            ->values();
    }

    public function usersForDivision(?string $division): Collection
    {
        if (!$division) {
            return collect();
        }

        return User::query()
            ->where('is_active', true)
            ->get()
            ->filter(fn (User $user) => $user->divisionKey() === $division)
            ->values();
    }

    private function exists(User $user, string $key): bool
    {
        return $user->notifications()
            ->where('data', 'like', '%"key":"' . addcslashes($key, '%_\\') . '"%')
            ->exists();
    }
}
