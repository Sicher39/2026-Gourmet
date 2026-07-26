<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\CookieSetting;
use Illuminate\Auth\Access\HandlesAuthorization;

class CookieSettingPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:CookieSetting');
    }

    public function view(AuthUser $authUser, CookieSetting $cookieSetting): bool
    {
        return $authUser->can('View:CookieSetting');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:CookieSetting');
    }

    public function update(AuthUser $authUser, CookieSetting $cookieSetting): bool
    {
        return $authUser->can('Update:CookieSetting');
    }

    public function delete(AuthUser $authUser, CookieSetting $cookieSetting): bool
    {
        return $authUser->can('Delete:CookieSetting');
    }

    public function restore(AuthUser $authUser, CookieSetting $cookieSetting): bool
    {
        return $authUser->can('Restore:CookieSetting');
    }

    public function forceDelete(AuthUser $authUser, CookieSetting $cookieSetting): bool
    {
        return $authUser->can('ForceDelete:CookieSetting');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:CookieSetting');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:CookieSetting');
    }

    public function replicate(AuthUser $authUser, CookieSetting $cookieSetting): bool
    {
        return $authUser->can('Replicate:CookieSetting');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:CookieSetting');
    }

}