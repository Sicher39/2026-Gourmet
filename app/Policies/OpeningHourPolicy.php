<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\OpeningHour;
use App\Models\User as AuthUser;
use Illuminate\Auth\Access\HandlesAuthorization;

class OpeningHourPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return ($authUser->can('ViewAny:OpeningHour')
            || $authUser->can('View:OpeningHour')
            || $authUser->can('Update:OpeningHour'))
            && ($authUser->isSuperAdmin() || $authUser->managedRestaurants()->exists());
    }

    public function view(AuthUser $authUser, OpeningHour $openingHour): bool
    {
        return ($authUser->can('View:OpeningHour') || $authUser->can('Update:OpeningHour'))
            && $this->canAccessOpeningHour($authUser, $openingHour);
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->isSuperAdmin()
            && $authUser->can('Create:OpeningHour');
    }

    public function update(AuthUser $authUser, OpeningHour $openingHour): bool
    {
        return $authUser->can('Update:OpeningHour')
            && $this->canAccessOpeningHour($authUser, $openingHour);
    }

    public function delete(AuthUser $authUser, OpeningHour $openingHour): bool
    {
        return $authUser->can('Delete:OpeningHour')
            && $this->canAccessOpeningHour($authUser, $openingHour);
    }

    public function restore(AuthUser $authUser, OpeningHour $openingHour): bool
    {
        return $authUser->can('Restore:OpeningHour');
    }

    public function forceDelete(AuthUser $authUser, OpeningHour $openingHour): bool
    {
        return $authUser->can('ForceDelete:OpeningHour');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:OpeningHour');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:OpeningHour');
    }

    public function replicate(AuthUser $authUser, OpeningHour $openingHour): bool
    {
        return $authUser->can('Replicate:OpeningHour');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:OpeningHour');
    }

    private function canAccessOpeningHour(AuthUser $authUser, OpeningHour $openingHour): bool
    {
        if ($authUser->isSuperAdmin()) {
            return true;
        }

        return ($openingHour->show_on_ponavka && $authUser->managesPonavka())
            || ($openingHour->show_on_vankovka && $authUser->managesVankovka());
    }
}
