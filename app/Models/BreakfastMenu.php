<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BreakfastMenu extends Model
{
    protected $fillable = ['restaurant_contact_information_id', 'valid_from', 'valid_to', 'is_active'];

    protected function casts(): array
    {
        return [
            'valid_from' => 'date',
            'valid_to' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(RestaurantContactInformation::class, 'restaurant_contact_information_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(BreakfastMenuItem::class)->orderBy('sort_order');
    }
}
