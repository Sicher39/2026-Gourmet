<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\NonCookingDay;
use App\Models\User;

class NonCookingDayPolicy
{
    public function before(User $user): ?bool
    {
        return $user->hasRole('super_admin') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('ViewAny:NonCookingDay');
    }

    public function view(User $user, NonCookingDay $nonCookingDay): bool
    {
        return $user->can('View:NonCookingDay');
    }

    public function create(User $user): bool
    {
        return $user->can('Create:NonCookingDay');
    }

    public function update(User $user, NonCookingDay $nonCookingDay): bool
    {
        return $user->can('Update:NonCookingDay');
    }

    public function delete(User $user, NonCookingDay $nonCookingDay): bool
    {
        return $user->can('Delete:NonCookingDay');
    }
}
