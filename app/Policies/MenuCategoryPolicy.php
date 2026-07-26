<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\MenuCategory;
use Illuminate\Auth\Access\HandlesAuthorization;

class MenuCategoryPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:MenuCategory');
    }

    public function view(AuthUser $authUser, MenuCategory $menuCategory): bool
    {
        return $authUser->can('View:MenuCategory');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:MenuCategory');
    }

    public function update(AuthUser $authUser, MenuCategory $menuCategory): bool
    {
        return $authUser->can('Update:MenuCategory');
    }

    public function delete(AuthUser $authUser, MenuCategory $menuCategory): bool
    {
        return $authUser->can('Delete:MenuCategory');
    }

    public function restore(AuthUser $authUser, MenuCategory $menuCategory): bool
    {
        return $authUser->can('Restore:MenuCategory');
    }

    public function forceDelete(AuthUser $authUser, MenuCategory $menuCategory): bool
    {
        return $authUser->can('ForceDelete:MenuCategory');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:MenuCategory');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:MenuCategory');
    }

    public function replicate(AuthUser $authUser, MenuCategory $menuCategory): bool
    {
        return $authUser->can('Replicate:MenuCategory');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:MenuCategory');
    }

}