<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Compliance\LegalBasis;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DataProcessingPurpose extends Model
{
    protected $fillable = [
        'name',
        'context',
        'description',
        'personal_data_categories',
        'legal_basis',
        'retention_period',
        'recipients',
        'third_country_transfer',
        'is_active',
        'priority',
    ];

    protected $casts = [
        'legal_basis' => LegalBasis::class,
        'is_active' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
