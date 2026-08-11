<?php

namespace App\Filament\Restaurant\Resources\MenuCatalogItemResource\Pages;

use App\Filament\Restaurant\Resources\MenuCatalogItemResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;

class CreateMenuCatalogItem extends CreateRecord
{
    protected static string $resource = MenuCatalogItemResource::class;

    public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }

    protected function fillForm(): void
    {
        parent::fillForm();

        $catalogTypeId = static::getResource()::catalogTypeIdFromRequest();

        if ($catalogTypeId === null) {
            return;
        }

        $this->form->fill([
            ...$this->form->getRawState(),
            'menu_catalog_type_id' => $catalogTypeId,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $catalogTypeId = static::getResource()::catalogTypeIdFromRequest();

        if ($catalogTypeId !== null) {
            $data['menu_catalog_type_id'] = $catalogTypeId;
        }

        return $data;
    }
}
