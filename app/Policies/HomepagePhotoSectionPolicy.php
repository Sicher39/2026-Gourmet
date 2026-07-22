<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\HomepagePhotoSection;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class HomepagePhotoSectionPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:HomepagePhotoSection');
    }

    public function view(AuthUser $authUser, HomepagePhotoSection $homepagePhotoSection): bool
    {
        return $authUser->can('View:HomepagePhotoSection');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:HomepagePhotoSection');
    }

    public function update(AuthUser $authUser, HomepagePhotoSection $homepagePhotoSection): bool
    {
        return $authUser->can('Update:HomepagePhotoSection');
    }

    public function delete(AuthUser $authUser, HomepagePhotoSection $homepagePhotoSection): bool
    {
        return $authUser->can('Delete:HomepagePhotoSection');
    }

    public function restore(AuthUser $authUser, HomepagePhotoSection $homepagePhotoSection): bool
    {
        return $authUser->can('Restore:HomepagePhotoSection');
    }

    public function forceDelete(AuthUser $authUser, HomepagePhotoSection $homepagePhotoSection): bool
    {
        return $authUser->can('ForceDelete:HomepagePhotoSection');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:HomepagePhotoSection');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:HomepagePhotoSection');
    }

    public function replicate(AuthUser $authUser, HomepagePhotoSection $homepagePhotoSection): bool
    {
        return $authUser->can('Replicate:HomepagePhotoSection');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:HomepagePhotoSection');
    }
}
