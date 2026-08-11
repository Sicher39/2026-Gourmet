<?php

declare(strict_types=1);

namespace App\Models;

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
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function catalogItems(): HasMany
    {
        return $this->hasMany(MenuCatalogItem::class, 'menu_catalog_type_id');
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
