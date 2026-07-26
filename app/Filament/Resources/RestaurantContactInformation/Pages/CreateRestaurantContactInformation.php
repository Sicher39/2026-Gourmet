<?php

declare(strict_types=1);

namespace App\Filament\Resources\RestaurantContactInformation\Pages;

use App\Filament\Resources\RestaurantContactInformation\RestaurantContactInformationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRestaurantContactInformation extends CreateRecord
{
    protected static string $resource = RestaurantContactInformationResource::class;
}
