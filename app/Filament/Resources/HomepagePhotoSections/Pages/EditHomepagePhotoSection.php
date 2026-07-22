<?php

declare(strict_types=1);

namespace App\Filament\Resources\HomepagePhotoSections\Pages;

use App\Filament\Resources\HomepagePhotoSections\HomepagePhotoSectionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditHomepagePhotoSection extends EditRecord
{
    protected static string $resource = HomepagePhotoSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
