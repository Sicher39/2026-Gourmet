<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RestaurantMenuSection extends Model
{
    protected $table = 'restaurant_menu_sections';

    protected $fillable = [
        'restaurant_menu_id',
        'menu_category_id',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function restaurantMenu(): BelongsTo
    {
        return $this->belongsTo(RestaurantMenu::class, 'restaurant_menu_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(MenuCategory::class, 'menu_category_id');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(RestaurantMenuEntry::class, 'restaurant_menu_section_id')->orderBy('sort_order');
    }
}
