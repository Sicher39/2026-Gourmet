<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MenuItemType;
use App\Services\Menu\PlannedMenuService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlannedMenuItem extends Model
{
    protected static function booted(): void
    {
        static::created(fn (PlannedMenuItem $item) => app(PlannedMenuService::class)->createMissingBranchVariants($item));
    }

    protected $fillable = ['planned_menu_day_id', 'type', 'menu_product_id', 'amount', 'menu_unit_id', 'default_price', 'sort_order'];

    protected function casts(): array
    {
        return ['type' => MenuItemType::class, 'amount' => 'decimal:3', 'default_price' => 'decimal:2', 'sort_order' => 'integer'];
    }

    public function day(): BelongsTo
    {
        return $this->belongsTo(PlannedMenuDay::class, 'planned_menu_day_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(MenuProduct::class, 'menu_product_id');
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
