<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Compliance\ConsentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsentRecord extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'consent_uuid',
        'type',
        'version',
        'preferences',
        'ip_hash',
        'user_agent_hash',
        'accepted_at',
        'rejected_at',
        'updated_at',
        'withdrawn_at',
        'created_at',
        'source',
        'purpose',
        'subject_name',
        'subject_email',
        'subject_phone',
        'data_processing_purpose_id',
        'legal_document_id',
        'channel',
    ];

    protected $casts = [
        'type' => ConsentType::class,
        'preferences' => 'array',
        'accepted_at' => 'datetime',
        'rejected_at' => 'datetime',
        'updated_at' => 'datetime',
        'withdrawn_at' => 'datetime',
        'created_at' => 'datetime',
        'data_processing_purpose_id' => 'integer',
        'legal_document_id' => 'integer',
    ];

    public function dataProcessingPurpose(): BelongsTo
    {
        return $this->belongsTo(DataProcessingPurpose::class, 'data_processing_purpose_id');
    }

    public function legalDocument(): BelongsTo
    {
        return $this->belongsTo(LegalDocument::class, 'legal_document_id');
    }
}
