<?php

namespace App\Filament\Restaurant\Resources\MenuCatalogTypeResource\Pages;

use App\Filament\Restaurant\Resources\MenuCatalogTypeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMenuCatalogType extends CreateRecord
{
    protected static string $resource = MenuCatalogTypeResource::class;

    protected function fillForm(): void
    {
        parent::fillForm();

        $kind = MenuCatalogTypeResource::kindFromRequest();

        if ($kind === null) {
            return;
        }

        $this->form->fill([
            ...$this->form->getRawState(),
            'menu_kind' => $kind,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $kind = MenuCatalogTypeResource::kindFromRequest();

        if ($kind !== null) {
            $data['menu_kind'] = $kind;
        }

        return $data;
    }
}
