<?php

declare(strict_types=1);

namespace App\Filament\Restaurant\Resources\CookResource\Pages;

use App\Filament\Restaurant\Resources\CookResource;
use App\Models\Cook;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditCook extends EditRecord
{
    protected static string $resource = CookResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        foreach ([
            'show_on_homepage' => 'Úvodní stránce',
            'show_on_ponavka' => 'Ponávce',
            'show_on_vankovka' => 'Vaňkovce',
        ] as $field => $page) {
            if (! ($data[$field] ?? false)) {
                continue;
            }

            if (Cook::query()
                ->whereKeyNot($this->record)
                ->where($field, true)
                ->count() >= Cook::MAXIMUM_COOKS_PER_PAGE) {
                throw ValidationException::withMessages([
                    $field => 'Na '.$page.' mohou být nejvýše '.Cook::MAXIMUM_COOKS_PER_PAGE.' kuchaři.',
                ]);
            }
        }

        return $data;
    }
}
