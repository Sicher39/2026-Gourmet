<?php

namespace App\Filament\Restaurant\Resources\BeverageRestaurantMenuResource\Pages;

use App\Filament\Restaurant\Resources\BeverageRestaurantMenuResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBeverageRestaurantMenu extends EditRecord
{
    protected static string $resource = BeverageRestaurantMenuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
