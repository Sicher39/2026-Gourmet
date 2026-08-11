<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Cook extends Model
{
    public const MAXIMUM_COOKS = 9;

    public const MAXIMUM_COOKS_PER_PAGE = 3;

    protected $fillable = [
        'name',
        'image',
        'show_on_homepage',
        'show_on_ponavka',
        'show_on_vankovka',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'show_on_homepage' => 'boolean',
            'show_on_ponavka' => 'boolean',
            'show_on_vankovka' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @param  Builder<Cook>  $query
     */
    public function scopeForHomepage(Builder $query): void
    {
        $query->where('show_on_homepage', true);
    }

    /**
     * @param  Builder<Cook>  $query
     */
    public function scopeForPonavka(Builder $query): void
    {
        $query->where('show_on_ponavka', true);
    }

    /**
     * @param  Builder<Cook>  $query
     */
    public function scopeForVankovka(Builder $query): void
    {
        $query->where('show_on_vankovka', true);
    }

    public function imageUrl(): string
    {
        return Storage::disk('public')->url($this->image);
    }
}
