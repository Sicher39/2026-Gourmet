<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\SeoPage;
use Illuminate\Auth\Access\HandlesAuthorization;

class SeoPagePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:SeoPage');
    }

    public function view(AuthUser $authUser, SeoPage $seoPage): bool
    {
        return $authUser->can('View:SeoPage');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:SeoPage');
    }

    public function update(AuthUser $authUser, SeoPage $seoPage): bool
    {
        return $authUser->can('Update:SeoPage');
    }

    public function delete(AuthUser $authUser, SeoPage $seoPage): bool
    {
        return $authUser->can('Delete:SeoPage');
    }

    public function restore(AuthUser $authUser, SeoPage $seoPage): bool
    {
        return $authUser->can('Restore:SeoPage');
    }

    public function forceDelete(AuthUser $authUser, SeoPage $seoPage): bool
    {
        return $authUser->can('ForceDelete:SeoPage');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:SeoPage');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:SeoPage');
    }

    public function replicate(AuthUser $authUser, SeoPage $seoPage): bool
    {
        return $authUser->can('Replicate:SeoPage');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:SeoPage');
    }

}