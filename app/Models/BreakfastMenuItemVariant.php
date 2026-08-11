<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BreakfastMenuItemVariant extends Model
{
    protected $fillable = ['breakfast_menu_item_id', 'name', 'allergens_snapshot', 'sort_order'];

    protected function casts(): array
    {
        return [
            'allergens_snapshot' => 'array',
            'sort_order' => 'integer',
        ];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(BreakfastMenuItem::class, 'breakfast_menu_item_id');
    }
}
