<?php

namespace App\Filament\Restaurant\Resources\DeliveryServiceResource\Pages;

use App\Filament\Restaurant\Resources\DeliveryServiceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDeliveryServices extends ListRecords
{
    protected static string $resource = DeliveryServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
