<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\RestaurantMenu;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class RestaurantMenuPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:RestaurantMenu');
    }

    public function view(AuthUser $authUser, RestaurantMenu $restaurantMenu): bool
    {
        return $authUser->can('View:RestaurantMenu');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:RestaurantMenu');
    }

    public function update(AuthUser $authUser, RestaurantMenu $restaurantMenu): bool
    {
        return $authUser->can('Update:RestaurantMenu');
    }

    public function delete(AuthUser $authUser, RestaurantMenu $restaurantMenu): bool
    {
        return $authUser->can('Delete:RestaurantMenu');
    }

    public function restore(AuthUser $authUser, RestaurantMenu $restaurantMenu): bool
    {
        return $authUser->can('Restore:RestaurantMenu');
    }

    public function forceDelete(AuthUser $authUser, RestaurantMenu $restaurantMenu): bool
    {
        return $authUser->can('ForceDelete:RestaurantMenu');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:RestaurantMenu');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:RestaurantMenu');
    }

    public function replicate(AuthUser $authUser, RestaurantMenu $restaurantMenu): bool
    {
        return $authUser->can('Replicate:RestaurantMenu');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:RestaurantMenu');
    }
}
