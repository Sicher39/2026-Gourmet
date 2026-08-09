<?php

declare(strict_types=1);

namespace App\Filament\Restaurant\Resources\NonCookingDayResource\Pages;

use App\Filament\Restaurant\Resources\NonCookingDayResource;
use Filament\Resources\Pages\CreateRecord;

class CreateNonCookingDay extends CreateRecord
{
    protected static string $resource = NonCookingDayResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        return $data;
    }
}
