<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MenuAllergen extends Model
{
    use SoftDeletes;

    protected $table = 'menu_allergens';

    protected $fillable = [
        'code',
        'name',
        'description',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function catalogItems(): BelongsToMany
    {
        return $this->belongsToMany(MenuCatalogItem::class, 'menu_allergen_menu_catalog_item', 'menu_allergen_id', 'menu_catalog_item_id');
    }
}
