<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;

/**
 * Marks a model as owned by a restaurant branch through the
 * restaurant_contact_information_id column.
 */
trait BelongsToRestaurantBranch
{
    protected static function bootBelongsToRestaurantBranch(): void
    {
        static::saving(function (Model $record): void {
            $user = auth()->user();

            if (! $user instanceof User
                || $user->canManageSharedPlannedMenu()
                || $user->managesRestaurant((int) $record->getAttribute(static::restaurantBranchScopeColumn()))) {
                return;
            }

            throw new AuthorizationException;
        });
    }

    public static function restaurantBranchScopeColumn(): string
    {
        return 'restaurant_contact_information_id';
    }
}
