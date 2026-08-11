<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\DynamicGallery;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class DynamicGalleryPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:DynamicGallery');
    }

    public function view(AuthUser $authUser, DynamicGallery $dynamicGallery): bool
    {
        return $authUser->can('View:DynamicGallery');
    }

    public function create(AuthUser $authUser): bool
    {
        return false;
    }

    public function update(AuthUser $authUser, DynamicGallery $dynamicGallery): bool
    {
        return $authUser->can('Update:DynamicGallery');
    }

    public function delete(AuthUser $authUser, DynamicGallery $dynamicGallery): bool
    {
        return false;
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return false;
    }

    public function restore(AuthUser $authUser, DynamicGallery $dynamicGallery): bool
    {
        return false;
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return false;
    }

    public function forceDelete(AuthUser $authUser, DynamicGallery $dynamicGallery): bool
    {
        return false;
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return false;
    }

    public function replicate(AuthUser $authUser, DynamicGallery $dynamicGallery): bool
    {
        return false;
    }

    public function reorder(AuthUser $authUser): bool
    {
        return false;
    }
}
