<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ContentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class DynamicGallery extends Model
{
    protected $fillable = [
        'images',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'images' => 'array',
            'status' => ContentStatus::class,
        ];
    }

    /**
     * @param Builder<DynamicGallery> $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('status', ContentStatus::Published->value);
    }

    /**
     * @return array<int, string>
     */
    public function imageUrls(): array
    {
        return collect($this->images ?? [])
            ->filter(fn (mixed $path): bool => is_string($path) && filled($path))
            ->map(function (string $path): string {
                if (str_starts_with($path, 'img/')) {
                    return asset($path);
                }

                return Storage::disk('public')->url($path);
            })
            ->values()
            ->all();
    }
}
