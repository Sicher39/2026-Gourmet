<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MenuItemType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlannedMenuDay extends Model
{
    protected $fillable = ['planned_menu_id', 'date', 'is_non_cooking_day'];

    protected function casts(): array
    {
        return ['date' => 'date', 'is_non_cooking_day' => 'boolean'];
    }

    public function plannedMenu(): BelongsTo
    {
        return $this->belongsTo(PlannedMenu::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PlannedMenuItem::class)
            ->orderByRaw('CASE WHEN type = ? THEN 0 ELSE 1 END', [MenuItemType::Soup->value])
            ->orderBy('sort_order');
    }
}
