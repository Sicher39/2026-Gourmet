<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class HomepagePhotoSection extends Model
{
    protected $fillable = [
        'handle',
        'name',
        'is_active',
        'sort_order',
        'header',
        'note',
        'image_one_path',
        'image_two_path',
        'image_three_path',
        'button_label',
        'button_route_name',
        'button_url',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    /**
     * @return array{header: string, note: string, imageOne?: string|null, imageTwo?: string|null, imageThree?: string|null, button?: string|null, link?: string|null, url?: string|null}
     */
    public function frontendPayload(): array
    {
        return array_filter([
            'header' => $this->header,
            'note' => $this->note,
            'imageOne' => $this->publicImageUrl($this->image_one_path),
            'imageTwo' => $this->publicImageUrl($this->image_two_path),
            'imageThree' => $this->publicImageUrl($this->image_three_path),
            'button' => $this->button_label,
            'link' => $this->button_route_name,
            'url' => $this->button_url,
        ], static fn (mixed $value): bool => $value !== null && $value !== '');
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    private function publicImageUrl(?string $path): ?string
    {
        if (! filled($path)) {
            return null;
        }

        if (
            str_starts_with($path, '/')
            || str_starts_with($path, 'http://')
            || str_starts_with($path, 'https://')
        ) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }
}
