<?php

namespace App\Filament\Restaurant\Resources\MenuCatalogTypeResource\Pages;

use App\Filament\Restaurant\Resources\MenuCatalogTypeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMenuCatalogType extends EditRecord
{
    protected static string $resource = MenuCatalogTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
