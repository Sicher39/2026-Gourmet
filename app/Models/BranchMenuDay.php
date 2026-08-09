<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BranchMenuDay extends Model
{
    protected $fillable = ['branch_menu_id', 'date', 'is_non_cooking_day'];

    protected function casts(): array
    {
        return ['date' => 'date', 'is_non_cooking_day' => 'boolean'];
    }

    public function branchMenu(): BelongsTo
    {
        return $this->belongsTo(BranchMenu::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(BranchMenuItem::class)->orderBy('type')->orderBy('sort_order');
    }
}
