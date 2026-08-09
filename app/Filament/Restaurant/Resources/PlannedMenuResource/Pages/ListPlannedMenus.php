<?php

declare(strict_types=1);

namespace App\Filament\Restaurant\Resources\PlannedMenuResource\Pages;

use App\Filament\Restaurant\Resources\PlannedMenuResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPlannedMenus extends ListRecords
{
    protected static string $resource = PlannedMenuResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
