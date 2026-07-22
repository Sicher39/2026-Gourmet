<?php

declare(strict_types=1);

namespace App\Filament\Resources\EventGalleries\Pages;

use App\Filament\Resources\EventGalleries\EventGalleryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEventGalleries extends ListRecords
{
    protected static string $resource = EventGalleryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
