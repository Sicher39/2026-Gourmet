<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\MenuAllergen;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class MenuAllergenPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:MenuAllergen');
    }

    public function view(AuthUser $authUser, MenuAllergen $menuAllergen): bool
    {
        return $authUser->can('View:MenuAllergen');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:MenuAllergen');
    }

    public function update(AuthUser $authUser, MenuAllergen $menuAllergen): bool
    {
        return $authUser->can('Update:MenuAllergen');
    }

    public function delete(AuthUser $authUser, MenuAllergen $menuAllergen): bool
    {
        return $authUser->can('Delete:MenuAllergen');
    }

    public function restore(AuthUser $authUser, MenuAllergen $menuAllergen): bool
    {
        return $authUser->can('Restore:MenuAllergen');
    }

    public function forceDelete(AuthUser $authUser, MenuAllergen $menuAllergen): bool
    {
        return $authUser->can('ForceDelete:MenuAllergen');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:MenuAllergen');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:MenuAllergen');
    }

    public function replicate(AuthUser $authUser, MenuAllergen $menuAllergen): bool
    {
        return $authUser->can('Replicate:MenuAllergen');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:MenuAllergen');
    }
}
