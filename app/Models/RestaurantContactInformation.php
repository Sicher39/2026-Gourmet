<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

    public function managers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'restaurant_contact_information_user')->withTimestamps();
    }

    public function plannedMenuBranches(): HasMany
    {
        return $this->hasMany(PlannedMenuBranch::class, 'restaurant_contact_information_id');
    }

    public function branchMenus(): HasMany
    {
        return $this->hasMany(BranchMenu::class, 'restaurant_contact_information_id');
    }
}
