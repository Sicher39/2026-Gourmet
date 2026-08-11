<?php

namespace App\Filament\Restaurant\Resources\MenuCatalogItemResource\Pages;

use App\Filament\Restaurant\Resources\MenuCatalogItemResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;

class EditMenuCatalogItem extends EditRecord
{
    protected static string $resource = MenuCatalogItemResource::class;

    public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
