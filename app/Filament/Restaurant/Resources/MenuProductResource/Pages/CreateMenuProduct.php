<?php

namespace App\Filament\Restaurant\Resources\MenuProductResource\Pages;

use App\Filament\Restaurant\Resources\MenuProductResource;
use App\Models\MenuProduct;
use Filament\Resources\Pages\CreateRecord;

class CreateMenuProduct extends CreateRecord
{
    protected static string $resource = MenuProductResource::class;

    protected function fillForm(): void
    {
        parent::fillForm();

        $categoryId = static::getResource()::categoryIdFromRequest();

        if ($categoryId === null) {
            return;
        }

        $this->form->fill([
            ...$this->form->getRawState(),
            'menu_category_id' => $categoryId,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $categoryId = static::getResource()::categoryIdFromRequest();

        if ($categoryId !== null) {
            $data['menu_category_id'] = $categoryId;
        }

        return $data;
    }

    protected function afterCreate(): void
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
