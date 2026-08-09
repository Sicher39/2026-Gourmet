<?php

declare(strict_types=1);

namespace App\Filament\Restaurant\Resources\NonCookingDayResource\Pages;

use App\Filament\Restaurant\Resources\NonCookingDayResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditNonCookingDay extends EditRecord
{
    protected static string $resource = NonCookingDayResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
