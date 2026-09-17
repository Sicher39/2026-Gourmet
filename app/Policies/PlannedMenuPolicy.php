<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\PlannedMenu;
use App\Models\User as AuthUser;
use Illuminate\Auth\Access\HandlesAuthorization;

class PlannedMenuPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        if ($authUser->canManageSharedPlannedMenu()) {
            return $authUser->can('ViewAny:PlannedMenu');
        }

        return $authUser->managedRestaurants()->exists()
            && ($authUser->can('ViewAny:PlannedMenu')
                || $authUser->can('View:PlannedMenu')
                || $authUser->can('Update:PlannedMenu'));
    }

    public function view(AuthUser $authUser, PlannedMenu $plannedMenu): bool
    {
        return ($authUser->can('View:PlannedMenu') || $authUser->can('Update:PlannedMenu'))
            && $this->canAccessRestaurantVariant($authUser, $plannedMenu);
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->canManageSharedPlannedMenu()
            && $authUser->can('Create:PlannedMenu');
    }

    public function update(AuthUser $authUser, PlannedMenu $plannedMenu): bool
    {
        return $plannedMenu->isDraft()
            && $authUser->can('Update:PlannedMenu')
            && $this->canAccessRestaurantVariant($authUser, $plannedMenu);
    }

    private function canAccessRestaurantVariant(AuthUser $authUser, PlannedMenu $plannedMenu): bool
    {
        if ($authUser->canManageSharedPlannedMenu()) {
            return true;
        }

        return $plannedMenu->branches()
            ->whereIn(
                'restaurant_contact_information_id',
                $authUser->managedRestaurants()->select('restaurant_contact_information.id'),
            )
            ->exists();
    }

    public function delete(AuthUser $authUser, PlannedMenu $plannedMenu): bool
    {
        return $authUser->can('Delete:PlannedMenu');
    }

    public function restore(AuthUser $authUser, PlannedMenu $plannedMenu): bool
    {
        return $authUser->can('Restore:PlannedMenu');
    }

    public function forceDelete(AuthUser $authUser, PlannedMenu $plannedMenu): bool
    {
        return $authUser->can('ForceDelete:PlannedMenu');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PlannedMenu');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PlannedMenu');
    }

    public function replicate(AuthUser $authUser, PlannedMenu $plannedMenu): bool
    {
        return $authUser->can('Replicate:PlannedMenu');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PlannedMenu');
    }

}