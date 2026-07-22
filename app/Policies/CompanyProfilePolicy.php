<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\CompanyProfile;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class CompanyProfilePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:CompanyProfile');
    }

    public function view(AuthUser $authUser, CompanyProfile $companyProfile): bool
    {
        return $authUser->can('View:CompanyProfile');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:CompanyProfile');
    }

    public function update(AuthUser $authUser, CompanyProfile $companyProfile): bool
    {
        return $authUser->can('Update:CompanyProfile');
    }

    public function delete(AuthUser $authUser, CompanyProfile $companyProfile): bool
    {
        return $authUser->can('Delete:CompanyProfile');
    }

    public function restore(AuthUser $authUser, CompanyProfile $companyProfile): bool
    {
        return $authUser->can('Restore:CompanyProfile');
    }

    public function forceDelete(AuthUser $authUser, CompanyProfile $companyProfile): bool
    {
        return $authUser->can('ForceDelete:CompanyProfile');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:CompanyProfile');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:CompanyProfile');
    }

    public function replicate(AuthUser $authUser, CompanyProfile $companyProfile): bool
    {
        return $authUser->can('Replicate:CompanyProfile');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:CompanyProfile');
    }
}
