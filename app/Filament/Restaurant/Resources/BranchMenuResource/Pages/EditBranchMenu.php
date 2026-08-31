<?php

declare(strict_types=1);

namespace App\Filament\Restaurant\Resources\BranchMenuResource\Pages;

use App\Filament\Restaurant\Resources\BranchMenuResource;
use App\Models\BranchMenuItem;
use Filament\Resources\Pages\EditRecord;

class EditBranchMenu extends EditRecord
{
    protected static string $resource = BranchMenuResource::class;

    protected function afterSave(): void
    {
        $this->syncComponentSelections($this->data);
    }

    /**
     * @param array<string|int, mixed> $state
     */
    private function syncComponentSelections(array $state): void
    {
        if (isset($state['id']) && is_array($state['sideItems'] ?? null)) {
            $item = BranchMenuItem::query()->find($state['id']);

            if ($item instanceof BranchMenuItem) {
                $this->syncComponentType($item, $state['sideItems'], 'side');
            }
        }

        if (isset($state['id']) && is_array($state['otherItems'] ?? null)) {
            $item = BranchMenuItem::query()->find($state['id']);

            if ($item instanceof BranchMenuItem) {
                $this->syncComponentType($item, $state['otherItems'], 'other');
            }
        }

        foreach ($state as $value) {
            if (is_array($value)) {
                $this->syncComponentSelections($value);
            }
        }
    }

    /**
     * @param array<int|string, mixed> $catalogItemIds
     */
    private function syncComponentType(BranchMenuItem $item, array $catalogItemIds, string $kind): void
    {
        $item->catalogItems()->where('kind', $kind)->delete();

        foreach (array_values(array_filter($catalogItemIds, 'is_numeric')) as $sortOrder => $catalogItemId) {
            $item->catalogItems()->create([
                'menu_catalog_item_id' => (int) $catalogItemId,
                'kind' => $kind,
                'sort_order' => $sortOrder,
            ]);
        }
    }
}
