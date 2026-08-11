<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\PlannedMenu;
use Illuminate\Auth\Access\HandlesAuthorization;

class PlannedMenuPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PlannedMenu');
    }

    public function view(AuthUser $authUser, PlannedMenu $plannedMenu): bool
    {
        return $authUser->can('View:PlannedMenu');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PlannedMenu');
    }

    public function update(AuthUser $authUser, PlannedMenu $plannedMenu): bool
    {
        return $authUser->can('Update:PlannedMenu');
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