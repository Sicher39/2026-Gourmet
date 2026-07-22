<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyProfile extends Model
{
    protected $fillable = [
        'company_name',
        'company_id_number',
        'vat_id',
        'address',
        'street',
        'city',
        'zip',
        'country',
        'email',
        'phone',
        'bank_account',
        'logo',
        'logo_dark',
        'gdpr_effective_date',
    ];

    protected $casts = [
        'gdpr_effective_date' => 'date',
    ];

    public static function current(): ?self
    {
        return static::query()
            ->orderByRaw("CASE WHEN company_name IS NOT NULL AND company_name != '' THEN 0 ELSE 1 END")
            ->orderBy('id')
            ->first();
    }
}
