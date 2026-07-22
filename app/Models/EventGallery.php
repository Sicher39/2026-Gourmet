<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class EventGallery extends Model
{
    protected $fillable = [
        'title',
        'event_date',
        'photos',
        'is_active',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeNewestFirst(Builder $query): Builder
    {
        return $query
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    public function scopeNewestEventFirst(Builder $query): Builder
    {
        return $query
            ->orderByDesc('event_date')
            ->orderByDesc('id');
    }

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'photos' => 'array',
            'is_active' => 'boolean',
        ];
    }
}
