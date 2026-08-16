<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MenuItemType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlannedMenuItem extends Model
{
    protected $fillable = ['planned_menu_id', 'planned_menu_day_id', 'type', 'menu_catalog_item_id', 'amount', 'menu_unit_id', 'default_price', 'sort_order'];

    protected function casts(): array
    {
        return ['type' => MenuItemType::class, 'amount' => 'decimal:3', 'default_price' => 'decimal:2', 'sort_order' => 'integer'];
    }

    public function plannedMenu(): BelongsTo
    {
        return $this->belongsTo(PlannedMenu::class);
    }

    public function day(): BelongsTo
    {
        return $this->belongsTo(PlannedMenuDay::class, 'planned_menu_day_id');
    }

    public function scheduledDays(): BelongsToMany
    {
        return $this->belongsToMany(PlannedMenuDay::class, 'planned_menu_common_item_days')->orderBy('date');
    }

    public function catalogItem(): BelongsTo
    {
        return $this->belongsTo(MenuCatalogItem::class, 'menu_catalog_item_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(MenuUnit::class, 'menu_unit_id');
    }

    public function branchVariants(): HasMany
    {
        return $this->hasMany(PlannedMenuItemBranch::class);
    }
}
