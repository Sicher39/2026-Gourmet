<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\NonCookingDay;
use Illuminate\Auth\Access\HandlesAuthorization;

class NonCookingDayPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:NonCookingDay');
    }

    public function view(AuthUser $authUser, NonCookingDay $nonCookingDay): bool
    {
        return $authUser->can('View:NonCookingDay');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:NonCookingDay');
    }

    public function update(AuthUser $authUser, NonCookingDay $nonCookingDay): bool
    {
        return $authUser->can('Update:NonCookingDay');
    }

    public function delete(AuthUser $authUser, NonCookingDay $nonCookingDay): bool
    {
        return $authUser->can('Delete:NonCookingDay');
    }

    public function restore(AuthUser $authUser, NonCookingDay $nonCookingDay): bool
    {
        return $authUser->can('Restore:NonCookingDay');
    }

    public function forceDelete(AuthUser $authUser, NonCookingDay $nonCookingDay): bool
    {
        return $authUser->can('ForceDelete:NonCookingDay');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:NonCookingDay');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:NonCookingDay');
    }

    public function replicate(AuthUser $authUser, NonCookingDay $nonCookingDay): bool
    {
        return $authUser->can('Replicate:NonCookingDay');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:NonCookingDay');
    }

}