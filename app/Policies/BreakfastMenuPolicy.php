<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\BreakfastMenu;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BreakfastMenuPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        if ($user->canManageSharedPlannedMenu()) {
            return $user->can('ViewAny:BreakfastMenu');
        }

        return $user->managedRestaurants()->exists()
            && ($user->can('ViewAny:BreakfastMenu')
                || $user->can('View:BreakfastMenu')
                || $user->can('Update:BreakfastMenu'));
    }

    public function view(User $user, BreakfastMenu $breakfastMenu): bool
    {
        return ($user->can('View:BreakfastMenu') || $user->can('Update:BreakfastMenu'))
            && $this->canAccessRestaurant($user, $breakfastMenu);
    }

    public function create(User $user): bool
    {
        return $user->can('Create:BreakfastMenu')
            && ($user->canManageSharedPlannedMenu() || $user->managedRestaurants()->exists());
    }

    public function update(User $user, BreakfastMenu $breakfastMenu): bool
    {
        return $user->can('Update:BreakfastMenu')
            && $this->canAccessRestaurant($user, $breakfastMenu);
    }

    public function delete(User $user, BreakfastMenu $breakfastMenu): bool
    {
        return $user->can('Delete:BreakfastMenu')
            && $this->canAccessRestaurant($user, $breakfastMenu);
    }

    private function canAccessRestaurant(User $user, BreakfastMenu $breakfastMenu): bool
    {
        return $user->canManageSharedPlannedMenu()
            || $user->managesRestaurant($breakfastMenu->restaurant_contact_information_id);
    }
}
