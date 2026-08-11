<?php

declare(strict_types=1);

namespace App\Filament\Restaurant\Resources\BreakfastCatalogItemResource\Pages;

use App\Filament\Restaurant\Resources\BreakfastCatalogItemResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBreakfastCatalogItem extends EditRecord
{
    protected static string $resource = BreakfastCatalogItemResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
