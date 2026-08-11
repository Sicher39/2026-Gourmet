<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\DynamicGallery;
use Illuminate\Auth\Access\HandlesAuthorization;

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
        return $authUser->can('Create:DynamicGallery');
    }

    public function update(AuthUser $authUser, DynamicGallery $dynamicGallery): bool
    {
        return $authUser->can('Update:DynamicGallery');
    }

    public function delete(AuthUser $authUser, DynamicGallery $dynamicGallery): bool
    {
        return $authUser->can('Delete:DynamicGallery');
    }

    public function restore(AuthUser $authUser, DynamicGallery $dynamicGallery): bool
    {
        return $authUser->can('Restore:DynamicGallery');
    }

    public function forceDelete(AuthUser $authUser, DynamicGallery $dynamicGallery): bool
    {
        return $authUser->can('ForceDelete:DynamicGallery');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:DynamicGallery');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:DynamicGallery');
    }

    public function replicate(AuthUser $authUser, DynamicGallery $dynamicGallery): bool
    {
        return $authUser->can('Replicate:DynamicGallery');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:DynamicGallery');
    }

}