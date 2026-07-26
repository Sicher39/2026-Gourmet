<?php

declare(strict_types=1);

namespace App\Filament\Resources\RestaurantContactInformation\Pages;

use App\Filament\Resources\RestaurantContactInformation\RestaurantContactInformationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRestaurantContactInformation extends EditRecord
{
    protected static string $resource = RestaurantContactInformationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
