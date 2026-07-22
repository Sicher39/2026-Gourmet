<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\MenuCatalogItem;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class MenuCatalogItemPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:MenuCatalogItem');
    }

    public function view(AuthUser $authUser, MenuCatalogItem $menuCatalogItem): bool
    {
        return $authUser->can('View:MenuCatalogItem');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:MenuCatalogItem');
    }

    public function update(AuthUser $authUser, MenuCatalogItem $menuCatalogItem): bool
    {
        return $authUser->can('Update:MenuCatalogItem');
    }

    public function delete(AuthUser $authUser, MenuCatalogItem $menuCatalogItem): bool
    {
        return $authUser->can('Delete:MenuCatalogItem');
    }

    public function restore(AuthUser $authUser, MenuCatalogItem $menuCatalogItem): bool
    {
        return $authUser->can('Restore:MenuCatalogItem');
    }

    public function forceDelete(AuthUser $authUser, MenuCatalogItem $menuCatalogItem): bool
    {
        return $authUser->can('ForceDelete:MenuCatalogItem');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:MenuCatalogItem');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:MenuCatalogItem');
    }

    public function replicate(AuthUser $authUser, MenuCatalogItem $menuCatalogItem): bool
    {
        return $authUser->can('Replicate:MenuCatalogItem');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:MenuCatalogItem');
    }
}
