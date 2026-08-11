<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BreakfastCatalogItem extends Model
{
    protected $fillable = ['name', 'default_price', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return [
            'default_price' => 'decimal:2',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function allergens(): BelongsToMany
    {
        return $this->belongsToMany(MenuAllergen::class, 'breakfast_catalog_item_menu_allergen');
    }

    public function menuItems(): HasMany
    {
        return $this->hasMany(BreakfastMenuItem::class);
    }
}
