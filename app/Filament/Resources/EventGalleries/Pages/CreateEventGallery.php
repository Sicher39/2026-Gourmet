<?php

declare(strict_types=1);

namespace App\Filament\Resources\EventGalleries\Pages;

use App\Filament\Resources\EventGalleries\EventGalleryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEventGallery extends CreateRecord
{
    protected static string $resource = EventGalleryResource::class;
}
