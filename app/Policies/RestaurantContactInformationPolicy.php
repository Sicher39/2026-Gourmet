<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\RestaurantContactInformation;
use Illuminate\Auth\Access\HandlesAuthorization;

class RestaurantContactInformationPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:RestaurantContactInformation');
    }

    public function view(AuthUser $authUser, RestaurantContactInformation $restaurantContactInformation): bool
    {
        return $authUser->can('View:RestaurantContactInformation');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:RestaurantContactInformation');
    }

    public function update(AuthUser $authUser, RestaurantContactInformation $restaurantContactInformation): bool
    {
        return $authUser->can('Update:RestaurantContactInformation');
    }

    public function delete(AuthUser $authUser, RestaurantContactInformation $restaurantContactInformation): bool
    {
        return $authUser->can('Delete:RestaurantContactInformation');
    }

    public function restore(AuthUser $authUser, RestaurantContactInformation $restaurantContactInformation): bool
    {
        return $authUser->can('Restore:RestaurantContactInformation');
    }

    public function forceDelete(AuthUser $authUser, RestaurantContactInformation $restaurantContactInformation): bool
    {
        return $authUser->can('ForceDelete:RestaurantContactInformation');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:RestaurantContactInformation');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:RestaurantContactInformation');
    }

    public function replicate(AuthUser $authUser, RestaurantContactInformation $restaurantContactInformation): bool
    {
        return $authUser->can('Replicate:RestaurantContactInformation');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:RestaurantContactInformation');
    }

}