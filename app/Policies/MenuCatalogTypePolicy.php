<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\MenuCatalogType;
use Illuminate\Auth\Access\HandlesAuthorization;

class MenuCatalogTypePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:MenuCatalogType');
    }

    public function view(AuthUser $authUser, MenuCatalogType $menuCatalogType): bool
    {
        return $authUser->can('View:MenuCatalogType');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:MenuCatalogType');
    }

    public function update(AuthUser $authUser, MenuCatalogType $menuCatalogType): bool
    {
        return $authUser->can('Update:MenuCatalogType');
    }

    public function delete(AuthUser $authUser, MenuCatalogType $menuCatalogType): bool
    {
        return $authUser->can('Delete:MenuCatalogType');
    }

    public function restore(AuthUser $authUser, MenuCatalogType $menuCatalogType): bool
    {
        return $authUser->can('Restore:MenuCatalogType');
    }

    public function forceDelete(AuthUser $authUser, MenuCatalogType $menuCatalogType): bool
    {
        return $authUser->can('ForceDelete:MenuCatalogType');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:MenuCatalogType');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:MenuCatalogType');
    }

    public function replicate(AuthUser $authUser, MenuCatalogType $menuCatalogType): bool
    {
        return $authUser->can('Replicate:MenuCatalogType');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:MenuCatalogType');
    }

}