<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\BranchMenu;
use App\Models\User as AuthUser;
use Illuminate\Auth\Access\HandlesAuthorization;

class BranchMenuPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:BranchMenu');
    }

    public function view(AuthUser $authUser, BranchMenu $branchMenu): bool
    {
        return $authUser->can('View:BranchMenu')
            && ($authUser->canManageSharedPlannedMenu() || $authUser->managesRestaurant($branchMenu->restaurant_contact_information_id));
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:BranchMenu');
    }

    public function update(AuthUser $authUser, BranchMenu $branchMenu): bool
    {
        return $branchMenu->isEditable()
            && $authUser->can('Update:BranchMenu')
            && ($authUser->canManageSharedPlannedMenu() || $authUser->managesRestaurant($branchMenu->restaurant_contact_information_id));
    }

    public function delete(AuthUser $authUser, BranchMenu $branchMenu): bool
    {
        return $authUser->can('Delete:BranchMenu');
    }

    public function restore(AuthUser $authUser, BranchMenu $branchMenu): bool
    {
        return $authUser->can('Restore:BranchMenu');
    }

    public function forceDelete(AuthUser $authUser, BranchMenu $branchMenu): bool
    {
        return $authUser->can('ForceDelete:BranchMenu');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:BranchMenu');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:BranchMenu');
    }

    public function replicate(AuthUser $authUser, BranchMenu $branchMenu): bool
    {
        return $authUser->can('Replicate:BranchMenu');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:BranchMenu');
    }

}