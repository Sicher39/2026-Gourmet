<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\RestaurantBirthday;
use Illuminate\Auth\Access\HandlesAuthorization;

class RestaurantBirthdayPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:RestaurantBirthday');
    }

    public function view(AuthUser $authUser, RestaurantBirthday $restaurantBirthday): bool
    {
        return $authUser->can('View:RestaurantBirthday');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:RestaurantBirthday');
    }

    public function update(AuthUser $authUser, RestaurantBirthday $restaurantBirthday): bool
    {
        return $authUser->can('Update:RestaurantBirthday');
    }

    public function delete(AuthUser $authUser, RestaurantBirthday $restaurantBirthday): bool
    {
        return $authUser->can('Delete:RestaurantBirthday');
    }

    public function restore(AuthUser $authUser, RestaurantBirthday $restaurantBirthday): bool
    {
        return $authUser->can('Restore:RestaurantBirthday');
    }

    public function forceDelete(AuthUser $authUser, RestaurantBirthday $restaurantBirthday): bool
    {
        return $authUser->can('ForceDelete:RestaurantBirthday');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:RestaurantBirthday');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:RestaurantBirthday');
    }

    public function replicate(AuthUser $authUser, RestaurantBirthday $restaurantBirthday): bool
    {
        return $authUser->can('Replicate:RestaurantBirthday');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:RestaurantBirthday');
    }

}