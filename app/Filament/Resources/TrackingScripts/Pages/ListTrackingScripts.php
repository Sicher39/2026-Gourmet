<?php

declare(strict_types=1);

namespace App\Filament\Resources\TrackingScripts\Pages;

use App\Filament\Resources\TrackingScripts\TrackingScriptResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTrackingScripts extends ListRecords
{
    protected static string $resource = TrackingScriptResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
