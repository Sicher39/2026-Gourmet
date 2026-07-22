<?php

namespace App\Filament\Restaurant\Resources\BeverageMenuResource\Pages;

use App\Filament\Restaurant\Resources\BeverageMenuResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBeverageMenu extends CreateRecord
{
    protected static string $resource = BeverageMenuResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['type'] = 'beverage';

        return $data;
    }
}
