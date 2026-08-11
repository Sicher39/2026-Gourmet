<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\DeliveryService;
use Illuminate\Auth\Access\HandlesAuthorization;

class DeliveryServicePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:DeliveryService');
    }

    public function view(AuthUser $authUser, DeliveryService $deliveryService): bool
    {
        return $authUser->can('View:DeliveryService');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:DeliveryService');
    }

    public function update(AuthUser $authUser, DeliveryService $deliveryService): bool
    {
        return $authUser->can('Update:DeliveryService');
    }

    public function delete(AuthUser $authUser, DeliveryService $deliveryService): bool
    {
        return $authUser->can('Delete:DeliveryService');
    }

    public function restore(AuthUser $authUser, DeliveryService $deliveryService): bool
    {
        return $authUser->can('Restore:DeliveryService');
    }

    public function forceDelete(AuthUser $authUser, DeliveryService $deliveryService): bool
    {
        return $authUser->can('ForceDelete:DeliveryService');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:DeliveryService');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:DeliveryService');
    }

    public function replicate(AuthUser $authUser, DeliveryService $deliveryService): bool
    {
        return $authUser->can('Replicate:DeliveryService');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:DeliveryService');
    }

}