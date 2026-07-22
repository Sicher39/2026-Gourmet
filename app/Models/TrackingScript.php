<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Compliance\ScriptPosition;
use App\Enums\Compliance\TrackingCategory;
use App\Enums\Compliance\TrackingProvider;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TrackingScript extends Model
{
    protected $fillable = [
        'name',
        'provider',
        'category',
        'position',
        'identifier',
        'code',
        'description',
        'provider_name',
        'provider_privacy_url',
        'enabled',
        'requires_consent',
        'priority',
        'only_paths',
        'except_paths',
    ];

    protected $casts = [
        'provider' => TrackingProvider::class,
        'category' => TrackingCategory::class,
        'position' => ScriptPosition::class,
        'enabled' => 'boolean',
        'requires_consent' => 'boolean',
        'only_paths' => 'array',
        'except_paths' => 'array',
    ];

    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('enabled', true);
    }

    public function scopeConsentRelevant(Builder $query): Builder
    {
        return $query->enabled()
            ->where('requires_consent', true)
            ->whereIn('category', TrackingCategory::optionalValues());
    }

    /**
     * @param  string|null  $companyName  Optional fallback provider name for first-party
     *                                    (Custom + Necessary) tracking scripts whose
     *                                    provider_name is null in the database.
     */
    public function toFrontendArray(?string $companyName = null): array
    {
        $providerName = $this->provider_name;

        if ($providerName === null
            && $this->provider === TrackingProvider::Custom
            && $this->category === TrackingCategory::Necessary
        ) {
            $providerName = $companyName;
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'provider' => $this->provider?->value,
            'category' => $this->category?->value,
            'position' => $this->position?->value,
            'identifier' => $this->identifier,
            'code' => $this->code,
            'description' => $this->description,
            'providerName' => $providerName,
            'providerPrivacyUrl' => $this->provider_privacy_url,
            'requiresConsent' => $this->requires_consent,
            'priority' => $this->priority,
            'onlyPaths' => $this->only_paths,
            'exceptPaths' => $this->except_paths,
        ];
    }

    /**
     * Return per-category counts of optional (consent-requiring, enabled)
     * tracking scripts so that cookie-banner descriptions can reflect the
     * actual number of scripts configured in each category.
     *
     * @return array{analytics?: int, marketing?: int, preferences?: int}
     */
    public static function categoryCountsForBanner(): array
    {
        return self::enabled()
            ->where('requires_consent', true)
            ->whereIn('category', TrackingCategory::optionalValues())
            ->selectRaw('category, COUNT(*) as count')
            ->groupBy('category')
            ->pluck('count', 'category')
            ->map(fn ($count) => (int) $count)
            ->all();
    }
}
