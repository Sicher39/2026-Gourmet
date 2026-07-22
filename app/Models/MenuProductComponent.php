<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuProductComponent extends Model
{
    protected $table = 'menu_product_components';

    protected $fillable = [
        'menu_product_id',
        'menu_catalog_type_id',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(MenuProduct::class, 'menu_product_id');
    }

    public function catalogType(): BelongsTo
    {
        return $this->belongsTo(MenuCatalogType::class, 'menu_catalog_type_id');
    }

    public function componentItems(): HasMany
    {
        return $this->hasMany(MenuProductComponentItem::class, 'menu_product_component_id')->orderBy('sort_order');
    }

    public function catalogItems(): BelongsToMany
    {
        return $this->belongsToMany(
            MenuCatalogItem::class,
            'menu_product_component_items',
            'menu_product_component_id',
            'menu_catalog_item_id',
        )->withTimestamps();
    }
}
