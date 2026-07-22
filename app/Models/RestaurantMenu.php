<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RestaurantMenu extends Model
{
    use SoftDeletes;

    protected $table = 'restaurant_menus';

    protected $fillable = [
        'name',
        'type',
        'status',
        'valid_from',
        'valid_to',
        'note',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'valid_from' => 'date',
            'valid_to' => 'date',
            'sort_order' => 'integer',
        ];
    }

    public function sections(): HasMany
    {
        return $this->hasMany(RestaurantMenuSection::class, 'restaurant_menu_id')->orderBy('sort_order');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(RestaurantMenuEntry::class, 'restaurant_menu_id')->orderBy('sort_order');
    }
}
