<?php

declare(strict_types=1);

namespace App\Filament\Restaurant\Resources\BreakfastMenuResource\Pages;

use App\Filament\Restaurant\Resources\BreakfastMenuResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;

class CreateBreakfastMenu extends CreateRecord
{
    protected static string $resource = BreakfastMenuResource::class;

    public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }

    /** @param array<string, mixed> $data */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();
        $restaurantId = (int) ($data['restaurant_contact_information_id'] ?? 0);

        abort_unless(
            $user instanceof User
                && ($user->canManageSharedPlannedMenu() || $user->managesRestaurant($restaurantId)),
            403,
        );

        return $data;
    }
}
