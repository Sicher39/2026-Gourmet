<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\DataProcessingPurpose;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class DataProcessingPurposePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:DataProcessingPurpose');
    }

    public function view(AuthUser $authUser, DataProcessingPurpose $dataProcessingPurpose): bool
    {
        return $authUser->can('View:DataProcessingPurpose');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:DataProcessingPurpose');
    }

    public function update(AuthUser $authUser, DataProcessingPurpose $dataProcessingPurpose): bool
    {
        return $authUser->can('Update:DataProcessingPurpose');
    }

    public function delete(AuthUser $authUser, DataProcessingPurpose $dataProcessingPurpose): bool
    {
        return $authUser->can('Delete:DataProcessingPurpose');
    }

    public function restore(AuthUser $authUser, DataProcessingPurpose $dataProcessingPurpose): bool
    {
        return $authUser->can('Restore:DataProcessingPurpose');
    }

    public function forceDelete(AuthUser $authUser, DataProcessingPurpose $dataProcessingPurpose): bool
    {
        return $authUser->can('ForceDelete:DataProcessingPurpose');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:DataProcessingPurpose');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:DataProcessingPurpose');
    }

    public function replicate(AuthUser $authUser, DataProcessingPurpose $dataProcessingPurpose): bool
    {
        return $authUser->can('Replicate:DataProcessingPurpose');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:DataProcessingPurpose');
    }
}
