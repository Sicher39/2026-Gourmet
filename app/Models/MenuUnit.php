<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MenuUnit extends Model
{
    use SoftDeletes;

    protected $table = 'menu_units';

    protected $fillable = [
        'name',
        'symbol',
        'type',
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

    public function catalogItems(): HasMany
    {
        return $this->hasMany(MenuCatalogItem::class, 'menu_unit_id');
    }
}
