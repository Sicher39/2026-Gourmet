<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MenuCatalogKind;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class MenuCategory extends Model
{
    use SoftDeletes;

    protected $table = 'menu_categories';

    protected $fillable = [
        'name',
        'slug',
        'menu_kind',
        'is_active',
        'sort_order',
    ];

    protected static function booted(): void
    {
        static::saving(function (MenuCategory $category): void {
            $category->slug = static::uniqueSlugForName((string) $category->name, $category->getKey());
        });
    }

    protected function casts(): array
    {
        return [
            'menu_kind' => MenuCatalogKind::class,
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(MenuProduct::class, 'menu_category_id');
    }

    public function menuEntries(): HasMany
    {
        return $this->hasMany(RestaurantMenuEntry::class, 'menu_category_id');
    }

    /**
     * @param  Builder<MenuCategory>  $query
     * @return Builder<MenuCategory>
     */
    public function scopeFood(Builder $query): Builder
    {
        return $query->where('menu_kind', MenuCatalogKind::Food->value);
    }

    /**
     * @param  Builder<MenuCategory>  $query
     * @return Builder<MenuCategory>
     */
    public function scopeBeverage(Builder $query): Builder
    {
        return $query->where('menu_kind', MenuCatalogKind::Beverage->value);
    }

    private static function uniqueSlugForName(string $name, mixed $ignoreId = null): string
    {
        $baseSlug = Str::slug($name) ?: 'kategorie';
        $slug = $baseSlug;
        $suffix = 2;

        while (static::withTrashed()
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
