<?php

namespace App\Filament\Restaurant\Resources\MenuCategoryResource\Pages;

use App\Filament\Restaurant\Resources\MenuCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMenuCategory extends EditRecord
{
    protected static string $resource = MenuCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
