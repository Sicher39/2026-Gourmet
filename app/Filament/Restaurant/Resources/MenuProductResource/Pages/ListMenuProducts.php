<?php

namespace App\Filament\Restaurant\Resources\MenuProductResource\Pages;

use App\Filament\Restaurant\Resources\MenuProductResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMenuProducts extends ListRecords
{
    protected static string $resource = MenuProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
