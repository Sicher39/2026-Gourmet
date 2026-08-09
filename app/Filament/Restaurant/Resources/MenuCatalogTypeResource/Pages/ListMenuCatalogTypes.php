<?php

namespace App\Filament\Restaurant\Resources\MenuCatalogTypeResource\Pages;

use App\Filament\Restaurant\Resources\MenuCatalogTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMenuCatalogTypes extends ListRecords
{
    protected static string $resource = MenuCatalogTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
