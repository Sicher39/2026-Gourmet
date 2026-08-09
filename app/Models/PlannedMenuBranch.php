<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlannedMenuBranch extends Model
{
    protected $fillable = ['planned_menu_id', 'restaurant_contact_information_id', 'branch_name_snapshot'];

    public function plannedMenu(): BelongsTo
    {
        return $this->belongsTo(PlannedMenu::class);
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(RestaurantContactInformation::class, 'restaurant_contact_information_id');
    }

    public function itemVariants(): HasMany
    {
        return $this->hasMany(PlannedMenuItemBranch::class);
    }
}
