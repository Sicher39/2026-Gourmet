<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\TrackingScript;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class TrackingScriptPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:TrackingScript');
    }

    public function view(AuthUser $authUser, TrackingScript $trackingScript): bool
    {
        return $authUser->can('View:TrackingScript');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:TrackingScript');
    }

    public function update(AuthUser $authUser, TrackingScript $trackingScript): bool
    {
        return $authUser->can('Update:TrackingScript');
    }

    public function delete(AuthUser $authUser, TrackingScript $trackingScript): bool
    {
        return $authUser->can('Delete:TrackingScript');
    }

    public function restore(AuthUser $authUser, TrackingScript $trackingScript): bool
    {
        return $authUser->can('Restore:TrackingScript');
    }

    public function forceDelete(AuthUser $authUser, TrackingScript $trackingScript): bool
    {
        return $authUser->can('ForceDelete:TrackingScript');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:TrackingScript');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:TrackingScript');
    }

    public function replicate(AuthUser $authUser, TrackingScript $trackingScript): bool
    {
        return $authUser->can('Replicate:TrackingScript');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:TrackingScript');
    }
}
