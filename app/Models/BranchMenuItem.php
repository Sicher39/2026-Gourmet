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
            if ($item->menu_product_id === null || ! $item->isDirty('menu_product_id')) {
                return;
            }

            $product = MenuProduct::query()->find($item->menu_product_id);

            if (! $product instanceof MenuProduct) {
                return;
            }

            $item->product_name_snapshot = $product->composeNameFromComponents();
            $item->allergens_snapshot = array_values($product->computeAllergenSnapshot());
        });
    }

    protected $fillable = ['branch_menu_day_id', 'source_planned_menu_item_id', 'type', 'menu_product_id', 'product_name_snapshot', 'amount', 'menu_unit_id', 'unit_symbol_snapshot', 'price', 'is_available', 'sort_order', 'allergens_snapshot'];

    protected function casts(): array
    {
        return [
            'type' => MenuItemType::class,
            'amount' => 'decimal:3',
            'price' => 'decimal:2',
            'is_available' => 'boolean',
            'sort_order' => 'integer',
            'allergens_snapshot' => 'array',
        ];
    }

    public function day(): BelongsTo
    {
        return $this->belongsTo(BranchMenuDay::class, 'branch_menu_day_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(MenuProduct::class, 'menu_product_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(MenuUnit::class, 'menu_unit_id');
    }

    public function catalogItems(): HasMany
    {
        return $this->hasMany(BranchMenuItemCatalogItem::class)->orderBy('sort_order');
    }
}
