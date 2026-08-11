<?php

declare(strict_types=1);

namespace App\Filament\Restaurant\Resources\BreakfastMenuResource\Pages;

use App\Filament\Restaurant\Resources\BreakfastMenuResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;

class CreateBreakfastMenu extends CreateRecord
{
    protected static string $resource = BreakfastMenuResource::class;

    public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }
}
