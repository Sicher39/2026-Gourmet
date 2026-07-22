<?php

declare(strict_types=1);

namespace App\Filament\Resources\EventGalleries\Pages;

use App\Filament\Resources\EventGalleries\EventGalleryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEventGallery extends EditRecord
{
    protected static string $resource = EventGalleryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
