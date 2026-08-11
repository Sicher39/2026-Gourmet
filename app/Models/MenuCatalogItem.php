<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MenuCatalogItem extends Model
{
    use SoftDeletes;

    protected $table = 'menu_catalog_items';

    protected $fillable = [
        'menu_catalog_type_id',
        'menu_unit_id',
        'name',
        'description',
        'amount',
        'default_price',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'amount' => 'decimal:3',
            'default_price' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    public function catalogType(): BelongsTo
    {
        return $this->belongsTo(MenuCatalogType::class, 'menu_catalog_type_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(MenuUnit::class, 'menu_unit_id');
    }

    public function allergens(): BelongsToMany
    {
        return $this->belongsToMany(MenuAllergen::class, 'menu_allergen_menu_catalog_item', 'menu_catalog_item_id', 'menu_allergen_id');
    }
}
