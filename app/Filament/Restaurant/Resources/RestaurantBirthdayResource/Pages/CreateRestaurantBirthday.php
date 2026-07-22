<?php

namespace App\Filament\Restaurant\Resources\RestaurantBirthdayResource\Pages;

use App\Filament\Restaurant\Resources\RestaurantBirthdayResource;
use App\Models\RestaurantBirthday;
use Filament\Resources\Pages\CreateRecord;

class CreateRestaurantBirthday extends CreateRecord
{
    protected static string $resource = RestaurantBirthdayResource::class;

    public function mount(): void
    {
        if (($birthday = RestaurantBirthday::current()) !== null) {
            $this->redirect(RestaurantBirthdayResource::getUrl('edit', ['record' => $birthday]), navigate: true);

            return;
        }

        parent::mount();
    }

    protected function getRedirectUrl(): string
    {
        return RestaurantBirthdayResource::getSingletonUrl();
    }
}
