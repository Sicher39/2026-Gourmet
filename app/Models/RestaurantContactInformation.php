<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RestaurantContactInformation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_profile_id',
        'business_name',
        'email',
        'phone',
        'street',
        'city',
        'zip_code',
        'country',
    ];

    public function companyProfile(): BelongsTo
    {
        return $this->belongsTo(CompanyProfile::class);
    }
}
