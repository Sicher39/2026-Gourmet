<?php

namespace App\Filament\Restaurant\Resources\MenuAllergenResource\Pages;

use App\Filament\Restaurant\Resources\MenuAllergenResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMenuAllergen extends EditRecord
{
    protected static string $resource = MenuAllergenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
