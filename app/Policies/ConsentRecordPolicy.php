<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ConsentRecord;
use Illuminate\Auth\Access\HandlesAuthorization;

class ConsentRecordPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ConsentRecord');
    }

    public function view(AuthUser $authUser, ConsentRecord $consentRecord): bool
    {
        return $authUser->can('View:ConsentRecord');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ConsentRecord');
    }

    public function update(AuthUser $authUser, ConsentRecord $consentRecord): bool
    {
        return $authUser->can('Update:ConsentRecord');
    }

    public function delete(AuthUser $authUser, ConsentRecord $consentRecord): bool
    {
        return $authUser->can('Delete:ConsentRecord');
    }

    public function restore(AuthUser $authUser, ConsentRecord $consentRecord): bool
    {
        return $authUser->can('Restore:ConsentRecord');
    }

    public function forceDelete(AuthUser $authUser, ConsentRecord $consentRecord): bool
    {
        return $authUser->can('ForceDelete:ConsentRecord');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ConsentRecord');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ConsentRecord');
    }

    public function replicate(AuthUser $authUser, ConsentRecord $consentRecord): bool
    {
        return $authUser->can('Replicate:ConsentRecord');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ConsentRecord');
    }

}