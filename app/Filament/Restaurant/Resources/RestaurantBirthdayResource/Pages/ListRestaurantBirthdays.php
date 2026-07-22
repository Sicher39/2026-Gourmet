<?php

namespace App\Filament\Restaurant\Resources\RestaurantBirthdayResource\Pages;

use App\Filament\Restaurant\Resources\RestaurantBirthdayResource;
use Filament\Resources\Pages\ListRecords;

class ListRestaurantBirthdays extends ListRecords
{
    protected static string $resource = RestaurantBirthdayResource::class;

    public function mount(): void
    {
        $this->redirect(RestaurantBirthdayResource::getSingletonUrl(), navigate: true);
    }
}
