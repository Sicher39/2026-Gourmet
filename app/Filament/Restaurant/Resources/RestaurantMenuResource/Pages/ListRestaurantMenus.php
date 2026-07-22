<?php

namespace App\Filament\Restaurant\Resources\RestaurantMenuResource\Pages;

use App\Filament\Restaurant\Resources\RestaurantMenuResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRestaurantMenus extends ListRecords
{
    protected static string $resource = RestaurantMenuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
