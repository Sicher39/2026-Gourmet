<?php

declare(strict_types=1);

namespace App\Filament\Support;

use App\Models\Concerns\BelongsToRestaurantBranch;
use App\Models\User;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;

abstract class BranchScopedResource extends Resource
{
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $model = static::getModel();
        $user = auth()->user();

        if (! in_array(BelongsToRestaurantBranch::class, class_uses_recursive($model), true)
            || ! $user instanceof User
            || $user->canManageSharedPlannedMenu()) {
            return $query;
        }

        return $query->whereIn(
            $query->getModel()->qualifyColumn($model::restaurantBranchScopeColumn()),
            $user->managedRestaurants()->select('restaurant_contact_information.id'),
        );
    }
}
