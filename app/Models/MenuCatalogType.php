<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MenuCatalogKind;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class MenuCatalogType extends Model
{
    use SoftDeletes;

    protected $table = 'menu_catalog_types';

    protected $fillable = [
        'name',
        'slug',
        'menu_kind',
        'is_active',
        'sort_order',
    ];

    protected static function booted(): void
    {
        static::saving(function (MenuCatalogType $catalogType): void {
            $catalogType->slug = static::uniqueSlugForName((string) $catalogType->name, $catalogType->getKey());
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

    public function scopeFood(Builder $query): Builder
    {
        return $query->where('menu_kind', MenuCatalogKind::Food->value);
    }

    public function scopeBeverage(Builder $query): Builder
    {
        return $query->where('menu_kind', MenuCatalogKind::Beverage->value);
    }

    public function catalogItems(): HasMany
    {
        return $this->hasMany(MenuCatalogItem::class, 'menu_catalog_type_id');
    }

    public function productComponents(): HasMany
    {
        return $this->hasMany(MenuProductComponent::class, 'menu_catalog_type_id');
    }

    private static function uniqueSlugForName(string $name, mixed $ignoreId = null): string
    {
        $baseSlug = Str::slug($name) ?: 'typ-katalogu';
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
