<?php

namespace App\Filament\Restaurant\Resources\MenuCategoryResource\Pages;

use App\Filament\Restaurant\Resources\MenuCategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMenuCategory extends CreateRecord
{
    protected static string $resource = MenuCategoryResource::class;

    protected function fillForm(): void
    {
        parent::fillForm();

        $menuKind = MenuCategoryResource::menuKindFromRequest();

        if ($menuKind === null) {
            return;
        }

        $this->form->fill([
            ...$this->form->getRawState(),
            'menu_kind' => $menuKind->value,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $menuKind = MenuCategoryResource::menuKindFromRequest();

        if ($menuKind !== null) {
            $data['menu_kind'] = $menuKind->value;
        }

        return $data;
    }
}
