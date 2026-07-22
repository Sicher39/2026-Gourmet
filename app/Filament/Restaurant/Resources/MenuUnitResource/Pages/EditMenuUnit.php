<?php

namespace App\Filament\Restaurant\Resources\MenuUnitResource\Pages;

use App\Filament\Restaurant\Resources\MenuUnitResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMenuUnit extends EditRecord
{
    protected static string $resource = MenuUnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
