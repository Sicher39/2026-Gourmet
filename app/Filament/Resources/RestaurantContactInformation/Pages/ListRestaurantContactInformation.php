<?php

declare(strict_types=1);

namespace App\Filament\Resources\RestaurantContactInformation\Pages;

use App\Filament\Resources\RestaurantContactInformation\RestaurantContactInformationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRestaurantContactInformation extends ListRecords
{
    protected static string $resource = RestaurantContactInformationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
