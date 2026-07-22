<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Compliance\LegalDocumentType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

class LegalDocument extends Model
{
    protected $fillable = [
        'type',
        'title',
        'slug',
        'content',
        'version',
        'effective_from',
        'is_published',
    ];

    protected $casts = [
        'type' => LegalDocumentType::class,
        'effective_from' => 'date',
        'is_published' => 'boolean',
    ];

    public function questionnaires(): BelongsToMany
    {
        return $this->belongsToMany(Questionnaire::class)
            ->withPivot(['is_required', 'sort_order'])
            ->withTimestamps()
            ->orderByPivot('sort_order')
            ->orderBy('questionnaires.title');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeEffective(Builder $query, ?Carbon $date = null): Builder
    {
        $effectiveDate = ($date ?? now())->toDateString();

        return $query->where(function (Builder $query) use ($effectiveDate): void {
            $query->whereNull('effective_from')
                ->orWhereDate('effective_from', '<=', $effectiveDate);
        });
    }
}
