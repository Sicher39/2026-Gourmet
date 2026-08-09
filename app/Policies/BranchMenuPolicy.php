<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\BranchMenu;
use App\Models\User;

class BranchMenuPolicy
{
    public function before(User $user): ?bool
    {
        return $user->hasRole('super_admin') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('ViewAny:BranchMenu');
    }

    public function view(User $user, BranchMenu $branchMenu): bool
    {
        return $user->can('View:BranchMenu') && $user->managesRestaurant($branchMenu->restaurant_contact_information_id);
    }

    public function update(User $user, BranchMenu $branchMenu): bool
    {
        return $branchMenu->isEditable()
            && $user->can('Update:BranchMenu')
            && $user->managesRestaurant($branchMenu->restaurant_contact_information_id);
    }

    public function delete(User $user, BranchMenu $branchMenu): bool
    {
        return false;
    }
}
