<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BreakfastMenuItem extends Model
{
    protected static function booted(): void
    {
        static::saving(function (BreakfastMenuItem $item): void {
            if ($item->breakfast_catalog_item_id === null || ! $item->isDirty('breakfast_catalog_item_id')) {
                return;
            }

            $catalogItem = BreakfastCatalogItem::query()
                ->with('allergens')
                ->find($item->breakfast_catalog_item_id);

            if (! $catalogItem instanceof BreakfastCatalogItem) {
                return;
            }

            $item->name_snapshot = $catalogItem->name;
            $item->allergens_snapshot = $catalogItem->allergens
                ->pluck('code')
                ->filter()
                ->sort()
                ->values()
                ->all();
            $item->price = $catalogItem->default_price;
        });
    }

    protected $fillable = ['breakfast_menu_id', 'breakfast_catalog_item_id', 'name_snapshot', 'allergens_snapshot', 'price', 'is_available', 'sort_order'];

    protected function casts(): array
    {
        return [
            'allergens_snapshot' => 'array',
            'price' => 'decimal:2',
            'is_available' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(BreakfastMenu::class, 'breakfast_menu_id');
    }

    public function catalogItem(): BelongsTo
    {
        return $this->belongsTo(BreakfastCatalogItem::class, 'breakfast_catalog_item_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(BreakfastMenuItemVariant::class)->orderBy('sort_order');
    }
}
