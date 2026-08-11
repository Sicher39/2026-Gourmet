<?php

declare(strict_types=1);

namespace App\Filament\Restaurant\Resources\BreakfastCatalogItemResource\Pages;

use App\Filament\Restaurant\Resources\BreakfastCatalogItemResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBreakfastCatalogItem extends CreateRecord
{
    protected static string $resource = BreakfastCatalogItemResource::class;
}
