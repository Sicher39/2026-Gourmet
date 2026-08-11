<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ContentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DeliveryService extends Model
{
    protected $fillable = [
        'name',
        'logo',
        'alt_text',
        'branch',
        'url',
        'status',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'status' => ContentStatus::class,
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @param Builder<DeliveryService> $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('status', ContentStatus::Published->value);
    }

    /**
     * @param Builder<DeliveryService> $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
