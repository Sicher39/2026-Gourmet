<?php

declare(strict_types=1);

namespace App\Filament\Restaurant\Resources\OpeningHourResource\Pages;

use App\Filament\Restaurant\Resources\OpeningHourResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOpeningHour extends CreateRecord
{
    protected static string $resource = OpeningHourResource::class;
}
