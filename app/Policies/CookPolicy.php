<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Cook;
use Illuminate\Auth\Access\HandlesAuthorization;

class CookPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Cook');
    }

    public function view(AuthUser $authUser, Cook $cook): bool
    {
        return $authUser->can('View:Cook');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Cook');
    }

    public function update(AuthUser $authUser, Cook $cook): bool
    {
        return $authUser->can('Update:Cook');
    }

    public function delete(AuthUser $authUser, Cook $cook): bool
    {
        return $authUser->can('Delete:Cook');
    }

    public function restore(AuthUser $authUser, Cook $cook): bool
    {
        return $authUser->can('Restore:Cook');
    }

    public function forceDelete(AuthUser $authUser, Cook $cook): bool
    {
        return $authUser->can('ForceDelete:Cook');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Cook');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Cook');
    }

    public function replicate(AuthUser $authUser, Cook $cook): bool
    {
        return $authUser->can('Replicate:Cook');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Cook');
    }

}