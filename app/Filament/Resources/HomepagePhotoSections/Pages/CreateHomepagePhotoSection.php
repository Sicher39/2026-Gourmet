<?php

declare(strict_types=1);

namespace App\Filament\Resources\HomepagePhotoSections\Pages;

use App\Filament\Resources\HomepagePhotoSections\HomepagePhotoSectionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateHomepagePhotoSection extends CreateRecord
{
    protected static string $resource = HomepagePhotoSectionResource::class;
}
