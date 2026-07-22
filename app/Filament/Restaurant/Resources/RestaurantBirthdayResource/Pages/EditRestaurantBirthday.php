<?php

namespace App\Filament\Restaurant\Resources\RestaurantBirthdayResource\Pages;

use App\Filament\Restaurant\Resources\RestaurantBirthdayResource;
use Filament\Resources\Pages\EditRecord;

class EditRestaurantBirthday extends EditRecord
{
    protected static string $resource = RestaurantBirthdayResource::class;

    protected function getRedirectUrl(): string
    {
        return RestaurantBirthdayResource::getSingletonUrl();
    }
}
