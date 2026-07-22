<?php

declare(strict_types=1);

namespace App\Filament\Resources\DataProcessingPurposes\Pages;

use App\Filament\Resources\DataProcessingPurposes\DataProcessingPurposeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDataProcessingPurposes extends ListRecords
{
    protected static string $resource = DataProcessingPurposeResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
