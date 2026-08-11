<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\OpeningHour;
use Illuminate\Auth\Access\HandlesAuthorization;

class OpeningHourPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:OpeningHour');
    }

    public function view(AuthUser $authUser, OpeningHour $openingHour): bool
    {
        return $authUser->can('View:OpeningHour');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:OpeningHour');
    }

    public function update(AuthUser $authUser, OpeningHour $openingHour): bool
    {
        return $authUser->can('Update:OpeningHour');
    }

    public function delete(AuthUser $authUser, OpeningHour $openingHour): bool
    {
        return $authUser->can('Delete:OpeningHour');
    }

    public function restore(AuthUser $authUser, OpeningHour $openingHour): bool
    {
        return $authUser->can('Restore:OpeningHour');
    }

    public function forceDelete(AuthUser $authUser, OpeningHour $openingHour): bool
    {
        return $authUser->can('ForceDelete:OpeningHour');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:OpeningHour');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:OpeningHour');
    }

    public function replicate(AuthUser $authUser, OpeningHour $openingHour): bool
    {
        return $authUser->can('Replicate:OpeningHour');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:OpeningHour');
    }

}