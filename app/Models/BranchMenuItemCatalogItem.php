<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BranchMenuItemCatalogItem extends Model
{
    protected static function booted(): void
    {
        static::saving(function (BranchMenuItemCatalogItem $item): void {
            if ($item->menu_catalog_item_id === null) {
                return;
            }

            $catalogItem = MenuCatalogItem::query()->with('allergens')->find($item->menu_catalog_item_id);

            if (! $catalogItem instanceof MenuCatalogItem) {
                return;
            }

            $item->name_snapshot = $catalogItem->name;
            $item->allergens_snapshot = $catalogItem->allergens->pluck('code')->filter()->sort()->values()->all();
        });

        static::saved(fn (BranchMenuItemCatalogItem $item) => $item->branchMenuItem?->refreshAllergenSnapshot());
        static::deleted(fn (BranchMenuItemCatalogItem $item) => $item->branchMenuItem?->refreshAllergenSnapshot());
    }

    protected $fillable = ['branch_menu_item_id', 'menu_catalog_item_id', 'kind', 'name_snapshot', 'allergens_snapshot', 'sort_order'];

    protected function casts(): array
    {
        return ['allergens_snapshot' => 'array', 'sort_order' => 'integer'];
    }

    public function branchMenuItem(): BelongsTo
    {
        return $this->belongsTo(BranchMenuItem::class);
    }

    public function catalogItem(): BelongsTo
    {
        return $this->belongsTo(MenuCatalogItem::class, 'menu_catalog_item_id');
    }
}
