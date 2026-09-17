<?php

namespace App\Providers;

use App\Models\Concerns\BelongsToRestaurantBranch;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(function (User $user, string $ability, mixed $record = null): ?bool {
            if (! $record instanceof Model
                || ! in_array(BelongsToRestaurantBranch::class, class_uses_recursive($record), true)
                || $user->canManageSharedPlannedMenu()) {
                return null;
            }

            return $user->managesRestaurant((int) $record->getAttribute($record::restaurantBranchScopeColumn()))
                ? null
                : false;
        });
    }
}
