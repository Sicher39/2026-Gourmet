<?php

declare(strict_types=1);

namespace App\Filament\Restaurant\Resources\BreakfastCatalogItemResource\Pages;

use App\Filament\Restaurant\Resources\BreakfastCatalogItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBreakfastCatalogItems extends ListRecords
{
    protected static string $resource = BreakfastCatalogItemResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
