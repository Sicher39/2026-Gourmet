<?php

namespace App\Filament\Restaurant\Resources\MenuUnitResource\Pages;

use App\Filament\Restaurant\Resources\MenuUnitResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMenuUnits extends ListRecords
{
    protected static string $resource = MenuUnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
