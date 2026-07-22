<?php

namespace App\Filament\Restaurant\Resources\MenuProductResource\Pages;

use App\Filament\Restaurant\Resources\MenuProductResource;
use App\Models\MenuProduct;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMenuProduct extends EditRecord
{
    protected static string $resource = MenuProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $this->refreshGeneratedName();
    }

    protected function refreshGeneratedName(): void
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
