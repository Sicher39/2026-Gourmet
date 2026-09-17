<?php

declare(strict_types=1);

namespace App\Filament\Restaurant\Resources\OpeningHourResource\Pages;

use App\Filament\Restaurant\Resources\OpeningHourResource;
use App\Models\User;
use Filament\Resources\Pages\EditRecord;

class EditOpeningHour extends EditRecord
{
    protected static string $resource = OpeningHourResource::class;

    /** @param array<string, mixed> $data */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $user = auth()->user();

        if (! $user instanceof User || $user->isSuperAdmin()) {
            return $data;
        }

        $data['show_on_ponavka'] = $this->record->show_on_ponavka;
        $data['show_on_vankovka'] = $this->record->show_on_vankovka;
        $data['show_on_delivery'] = $this->record->show_on_delivery;

        return $data;
    }
}
