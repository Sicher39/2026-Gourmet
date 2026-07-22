<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\MenuUnit;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class MenuUnitPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:MenuUnit');
    }

    public function view(AuthUser $authUser, MenuUnit $menuUnit): bool
    {
        return $authUser->can('View:MenuUnit');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:MenuUnit');
    }

    public function update(AuthUser $authUser, MenuUnit $menuUnit): bool
    {
        return $authUser->can('Update:MenuUnit');
    }

    public function delete(AuthUser $authUser, MenuUnit $menuUnit): bool
    {
        return $authUser->can('Delete:MenuUnit');
    }

    public function restore(AuthUser $authUser, MenuUnit $menuUnit): bool
    {
        return $authUser->can('Restore:MenuUnit');
    }

    public function forceDelete(AuthUser $authUser, MenuUnit $menuUnit): bool
    {
        return $authUser->can('ForceDelete:MenuUnit');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:MenuUnit');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:MenuUnit');
    }

    public function replicate(AuthUser $authUser, MenuUnit $menuUnit): bool
    {
        return $authUser->can('Replicate:MenuUnit');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:MenuUnit');
    }
}
