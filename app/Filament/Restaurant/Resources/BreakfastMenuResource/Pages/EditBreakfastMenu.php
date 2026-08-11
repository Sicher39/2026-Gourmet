<?php

declare(strict_types=1);

namespace App\Filament\Restaurant\Resources\BreakfastMenuResource\Pages;

use App\Filament\Restaurant\Resources\BreakfastMenuResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;

class EditBreakfastMenu extends EditRecord
{
    protected static string $resource = BreakfastMenuResource::class;

    public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
