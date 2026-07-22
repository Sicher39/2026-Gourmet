<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuProductComponentItem extends Model
{
    protected $table = 'menu_product_component_items';

    protected $fillable = [
        'menu_product_component_id',
        'menu_catalog_item_id',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function component(): BelongsTo
    {
        return $this->belongsTo(MenuProductComponent::class, 'menu_product_component_id');
    }

    public function catalogItem(): BelongsTo
    {
        return $this->belongsTo(MenuCatalogItem::class, 'menu_catalog_item_id');
    }
}
