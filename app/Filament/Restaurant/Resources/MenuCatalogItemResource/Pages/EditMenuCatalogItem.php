<?php

namespace App\Filament\Restaurant\Resources\MenuCatalogItemResource\Pages;

use App\Filament\Restaurant\Resources\MenuCatalogItemResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMenuCatalogItem extends EditRecord
{
    protected static string $resource = MenuCatalogItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
