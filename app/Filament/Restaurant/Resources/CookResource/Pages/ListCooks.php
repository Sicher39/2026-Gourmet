<?php

declare(strict_types=1);

namespace App\Filament\Restaurant\Resources\CookResource\Pages;

use App\Filament\Restaurant\Resources\CookResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCooks extends ListRecords
{
    protected static string $resource = CookResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
