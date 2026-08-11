<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpeningHour extends Model
{
    protected $fillable = [
        'name',
        'opening_hours',
        'show_on_ponavka',
        'show_on_vankovka',
        'show_on_delivery',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'opening_hours' => 'array',
            'show_on_ponavka' => 'boolean',
            'show_on_vankovka' => 'boolean',
            'show_on_delivery' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
