<?php

namespace App\Filament\Restaurant\Resources\MenuAllergenResource\Pages;

use App\Filament\Restaurant\Resources\MenuAllergenResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMenuAllergens extends ListRecords
{
    protected static string $resource = MenuAllergenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
