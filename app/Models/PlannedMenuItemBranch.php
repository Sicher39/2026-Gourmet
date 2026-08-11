<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PlannedMenuItemBranch extends Model
{
    protected $fillable = ['planned_menu_item_id', 'planned_menu_branch_id', 'is_available'];

    protected function casts(): array
    {
        return ['is_available' => 'boolean'];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(PlannedMenuItem::class, 'planned_menu_item_id');
    }

    public function plannedBranch(): BelongsTo
    {
        return $this->belongsTo(PlannedMenuBranch::class, 'planned_menu_branch_id');
    }

    public function sideItems(): BelongsToMany
    {
        return $this->belongsToMany(MenuCatalogItem::class, 'planned_menu_item_branch_side_items');
    }

    public function otherItems(): BelongsToMany
    {
        return $this->belongsToMany(MenuCatalogItem::class, 'planned_menu_item_branch_other_items');
    }
}
