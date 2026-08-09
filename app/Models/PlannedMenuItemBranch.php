<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PlannedMenuItemBranch extends Model
{
    protected $fillable = ['planned_menu_item_id', 'planned_menu_branch_id', 'price', 'amount', 'menu_unit_id', 'is_available'];

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'amount' => 'decimal:3', 'is_available' => 'boolean'];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(PlannedMenuItem::class, 'planned_menu_item_id');
    }

    public function plannedBranch(): BelongsTo
    {
        return $this->belongsTo(PlannedMenuBranch::class, 'planned_menu_branch_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(MenuUnit::class, 'menu_unit_id');
    }

    public function catalogItems(): BelongsToMany
    {
        return $this->belongsToMany(MenuCatalogItem::class, 'planned_menu_item_branch_catalog_item')->withPivot('sort_order')->orderByPivot('sort_order');
    }
}
