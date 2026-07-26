<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\EventGallery;
use Illuminate\Auth\Access\HandlesAuthorization;

class EventGalleryPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:EventGallery');
    }

    public function view(AuthUser $authUser, EventGallery $eventGallery): bool
    {
        return $authUser->can('View:EventGallery');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:EventGallery');
    }

    public function update(AuthUser $authUser, EventGallery $eventGallery): bool
    {
        return $authUser->can('Update:EventGallery');
    }

    public function delete(AuthUser $authUser, EventGallery $eventGallery): bool
    {
        return $authUser->can('Delete:EventGallery');
    }

    public function restore(AuthUser $authUser, EventGallery $eventGallery): bool
    {
        return $authUser->can('Restore:EventGallery');
    }

    public function forceDelete(AuthUser $authUser, EventGallery $eventGallery): bool
    {
        return $authUser->can('ForceDelete:EventGallery');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:EventGallery');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:EventGallery');
    }

    public function replicate(AuthUser $authUser, EventGallery $eventGallery): bool
    {
        return $authUser->can('Replicate:EventGallery');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:EventGallery');
    }

}