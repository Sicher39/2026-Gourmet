<?php

declare(strict_types=1);

namespace App\Filament\Restaurant\Resources\DynamicGalleryResource\Pages;

use App\Filament\Restaurant\Resources\DynamicGalleryResource;
use Filament\Resources\Pages\EditRecord;

class EditDynamicGallery extends EditRecord
{
    protected static string $resource = DynamicGalleryResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
