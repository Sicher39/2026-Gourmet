<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\MenuProduct;
use Illuminate\Auth\Access\HandlesAuthorization;

class MenuProductPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:MenuProduct');
    }

    public function view(AuthUser $authUser, MenuProduct $menuProduct): bool
    {
        return $authUser->can('View:MenuProduct');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:MenuProduct');
    }

    public function update(AuthUser $authUser, MenuProduct $menuProduct): bool
    {
        return $authUser->can('Update:MenuProduct');
    }

    public function delete(AuthUser $authUser, MenuProduct $menuProduct): bool
    {
        return $authUser->can('Delete:MenuProduct');
    }

    public function restore(AuthUser $authUser, MenuProduct $menuProduct): bool
    {
        return $authUser->can('Restore:MenuProduct');
    }

    public function forceDelete(AuthUser $authUser, MenuProduct $menuProduct): bool
    {
        return $authUser->can('ForceDelete:MenuProduct');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:MenuProduct');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:MenuProduct');
    }

    public function replicate(AuthUser $authUser, MenuProduct $menuProduct): bool
    {
        return $authUser->can('Replicate:MenuProduct');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:MenuProduct');
    }

}