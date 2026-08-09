<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\BranchMenuStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BranchMenu extends Model
{
    protected $fillable = ['planned_menu_id', 'restaurant_contact_information_id', 'branch_name_snapshot', 'week_start', 'week_end', 'status', 'closed_at'];

    protected function casts(): array
    {
        return [
            'week_start' => 'date',
            'week_end' => 'date',
            'status' => BranchMenuStatus::class,
            'closed_at' => 'datetime',
        ];
    }

    public function plannedMenu(): BelongsTo
    {
        return $this->belongsTo(PlannedMenu::class);
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(RestaurantContactInformation::class, 'restaurant_contact_information_id');
    }

    public function days(): HasMany
    {
        return $this->hasMany(BranchMenuDay::class)->orderBy('date');
    }

    public function isEditable(): bool
    {
        return $this->status === BranchMenuStatus::Ready && today()->lessThanOrEqualTo($this->week_end);
    }
}
