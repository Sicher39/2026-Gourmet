<?php

namespace App\Filament\Restaurant\Resources\BeverageMenuResource\Pages;

use App\Filament\Restaurant\Resources\BeverageMenuResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBeverageMenus extends ListRecords
{
    protected static string $resource = BeverageMenuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
