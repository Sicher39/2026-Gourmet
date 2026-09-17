<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\BreakfastCatalogItem;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BreakfastCatalogItemPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('ViewAny:BreakfastCatalogItem')
            || $user->can('View:BreakfastCatalogItem')
            || $user->can('Update:BreakfastCatalogItem');
    }

    public function view(User $user, BreakfastCatalogItem $breakfastCatalogItem): bool
    {
        return $user->can('View:BreakfastCatalogItem')
            || $user->can('Update:BreakfastCatalogItem');
    }

    public function create(User $user): bool
    {
        return $user->can('Create:BreakfastCatalogItem');
    }

    public function update(User $user, BreakfastCatalogItem $breakfastCatalogItem): bool
    {
        return $user->can('Update:BreakfastCatalogItem');
    }

    public function delete(User $user, BreakfastCatalogItem $breakfastCatalogItem): bool
    {
        return $user->can('Delete:BreakfastCatalogItem');
    }
}
