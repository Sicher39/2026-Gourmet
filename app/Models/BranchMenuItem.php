<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MenuItemType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BranchMenuItem extends Model
{
    protected static function booted(): void
    {
        static::saving(function (BranchMenuItem $item): void {
            if ($item->menu_catalog_item_id !== null && $item->isDirty('menu_catalog_item_id')) {
                $catalogItem = MenuCatalogItem::query()->with('allergens')->find($item->menu_catalog_item_id);

                if ($catalogItem instanceof MenuCatalogItem) {
                    $item->item_name_snapshot = $catalogItem->name;
                }
            }

            if ($item->menu_unit_id !== null && $item->isDirty('menu_unit_id')) {
                $item->unit_symbol_snapshot = MenuUnit::query()->find($item->menu_unit_id)?->symbol;
            }
        });

        static::saved(fn (BranchMenuItem $item) => $item->refreshAllergenSnapshot());
    }

    protected $fillable = ['branch_menu_day_id', 'source_planned_menu_item_id', 'type', 'menu_catalog_item_id', 'item_name_snapshot', 'amount', 'menu_unit_id', 'unit_symbol_snapshot', 'price', 'is_available', 'show_on_web', 'sort_order', 'allergens_snapshot'];

    protected function casts(): array
    {
        return [
            'type' => MenuItemType::class,
            'amount' => 'decimal:3',
            'price' => 'decimal:2',
            'is_available' => 'boolean',
            'show_on_web' => 'boolean',
            'sort_order' => 'integer',
            'allergens_snapshot' => 'array',
        ];
    }

    public function day(): BelongsTo
    {
        return $this->belongsTo(BranchMenuDay::class, 'branch_menu_day_id');
    }

    public function catalogItem(): BelongsTo
    {
        return $this->belongsTo(MenuCatalogItem::class, 'menu_catalog_item_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(MenuUnit::class, 'menu_unit_id');
    }

    public function catalogItems(): HasMany
    {
        return $this->hasMany(BranchMenuItemCatalogItem::class)->orderBy('sort_order');
    }

    public function sideItems(): HasMany
    {
        return $this->hasMany(BranchMenuItemCatalogItem::class)->where('kind', 'side')->orderBy('sort_order');
    }

    public function otherItems(): HasMany
    {
        return $this->hasMany(BranchMenuItemCatalogItem::class)->where('kind', 'other')->orderBy('sort_order');
    }

    public function refreshAllergenSnapshot(): void
    {
        $baseAllergens = $this->catalogItem()
            ->with('allergens')
            ->first()
            ?->allergens
            ->pluck('code') ?? collect();
        $componentAllergens = $this->catalogItems()
            ->get()
            ->flatMap(fn (BranchMenuItemCatalogItem $item): array => $item->allergens_snapshot ?? []);

        $this->forceFill([
            'allergens_snapshot' => $baseAllergens
                ->merge($componentAllergens)
                ->filter()
                ->unique()
                ->sort()
                ->values()
                ->all(),
        ])->saveQuietly();
    }
}
