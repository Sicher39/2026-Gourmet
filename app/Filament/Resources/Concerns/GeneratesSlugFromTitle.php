<?php

declare(strict_types=1);

namespace App\Filament\Resources\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait GeneratesSlugFromTitle
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->withGeneratedSlug($data);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->withGeneratedSlug($data);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function withGeneratedSlug(array $data): array
    {
        if (! isset($data['title']) || ! is_string($data['title']) || trim($data['title']) === '') {
            return $data;
        }

        $record = $this->slugRecord();

        if ($record instanceof Model && ! $record->isDirty('title') && $record->getAttribute('title') === $data['title']) {
            $currentSlug = $record->getAttribute('slug');

            if (is_string($currentSlug) && trim($currentSlug) !== '') {
                $data['slug'] = $currentSlug;

                return $data;
            }
        }

        $data['slug'] = $this->uniqueSlugFromTitle($data['title']);

        return $data;
    }

    private function uniqueSlugFromTitle(string $title): string
    {
        $baseSlug = Str::slug($title);

        if ($baseSlug === '') {
            $baseSlug = Str::random(8);
        }

        $slug = $baseSlug;
        $suffix = 2;

        while ($this->slugExists($slug)) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    private function slugExists(string $slug): bool
    {
        $resource = static::$resource;
        $modelClass = $resource::getModel();

        $query = $modelClass::query()->where('slug', $slug);
        $record = $this->slugRecord();

        if ($record instanceof Model && $record->exists) {
            $query->where($record->getKeyName(), '!=', $record->getKey());
        }

        return $query->exists();
    }

    private function slugRecord(): ?Model
    {
        $record = property_exists($this, 'record') ? $this->record : null;

        return $record instanceof Model ? $record : null;
    }
}
