<?php

namespace App\Filament\Restaurant\Resources\RestaurantMenuResource\Pages;

use App\Filament\Restaurant\Resources\RestaurantMenuResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRestaurantMenu extends EditRecord
{
    protected static string $resource = RestaurantMenuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
