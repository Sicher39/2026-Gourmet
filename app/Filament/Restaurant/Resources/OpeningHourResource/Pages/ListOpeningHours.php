<?php

declare(strict_types=1);

namespace App\Filament\Restaurant\Resources\OpeningHourResource\Pages;

use App\Filament\Restaurant\Resources\OpeningHourResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOpeningHours extends ListRecords
{
    protected static string $resource = OpeningHourResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
