<?php

namespace App\Filament\Restaurant\Resources\BeverageMenuResource\Pages;

use App\Filament\Restaurant\Resources\BeverageMenuResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBeverageMenu extends EditRecord
{
    protected static string $resource = BeverageMenuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
