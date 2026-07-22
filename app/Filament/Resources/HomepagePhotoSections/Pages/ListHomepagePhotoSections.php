<?php

declare(strict_types=1);

namespace App\Filament\Resources\HomepagePhotoSections\Pages;

use App\Filament\Resources\HomepagePhotoSections\HomepagePhotoSectionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHomepagePhotoSections extends ListRecords
{
    protected static string $resource = HomepagePhotoSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
