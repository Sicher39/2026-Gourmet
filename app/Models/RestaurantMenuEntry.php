<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RestaurantMenuEntry extends Model
{
    use SoftDeletes;

    protected $table = 'restaurant_menu_entries';

    protected $fillable = [
        'restaurant_menu_id',
        'restaurant_menu_section_id',
        'menu_product_id',
        'menu_category_id',
        'price',
        'is_available',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_available' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function restaurantMenu(): BelongsTo
    {
        return $this->belongsTo(RestaurantMenu::class, 'restaurant_menu_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(RestaurantMenuSection::class, 'restaurant_menu_section_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(MenuProduct::class, 'menu_product_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(MenuCategory::class, 'menu_category_id');
    }
}
