<?php

namespace App\Filament\Restaurant\Resources\MenuProductResource\Pages;

use App\Filament\Restaurant\Resources\MenuProductResource;
use App\Models\MenuProduct;
use Filament\Resources\Pages\CreateRecord;

class CreateMenuProduct extends CreateRecord
{
    protected static string $resource = MenuProductResource::class;

    protected function afterCreate(): void
    {
        $product = $this->record;

        if (! $product instanceof MenuProduct) {
            return;
        }

        $generatedName = $product->composeNameFromComponents();

        if (trim($generatedName) === '') {
            return;
        }

        $product->name = $generatedName;
        $product->save();
    }
}
