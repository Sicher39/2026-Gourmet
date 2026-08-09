<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\PlannedMenu;
use App\Models\User;

class PlannedMenuPolicy
{
    public function before(User $user): ?bool
    {
        return $user->hasRole('super_admin') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('ViewAny:PlannedMenu');
    }

    public function view(User $user, PlannedMenu $plannedMenu): bool
    {
        return $user->can('View:PlannedMenu');
    }

    public function create(User $user): bool
    {
        return $user->canManageSharedPlannedMenu() && $user->can('Create:PlannedMenu');
    }

    public function update(User $user, PlannedMenu $plannedMenu): bool
    {
        if (! $plannedMenu->isDraft() || ! $user->can('Update:PlannedMenu')) {
            return false;
        }

        return $user->canManageSharedPlannedMenu()
            || $plannedMenu->branches()->whereHas('restaurant.managers', fn ($query) => $query->whereKey($user->getKey()))->exists();
    }

    public function delete(User $user, PlannedMenu $plannedMenu): bool
    {
        return $plannedMenu->isDraft() && $user->canManageSharedPlannedMenu() && $user->can('Delete:PlannedMenu');
    }
}
