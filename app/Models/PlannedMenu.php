<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PlannedMenuStatus;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlannedMenu extends Model
{
    protected $fillable = ['week_start', 'week_end', 'status', 'note', 'created_by', 'approved_by', 'approved_at'];

    protected function casts(): array
    {
        return [
            'week_start' => 'date',
            'week_end' => 'date',
            'status' => PlannedMenuStatus::class,
            'approved_at' => 'datetime',
        ];
    }

    public function branches(): HasMany
    {
        return $this->hasMany(PlannedMenuBranch::class);
    }

    public function days(): HasMany
    {
        return $this->hasMany(PlannedMenuDay::class)->orderBy('date');
    }

    public function commonItems(): HasMany
    {
        return $this->hasMany(PlannedMenuItem::class)->orderBy('sort_order');
    }

    public function branchMenus(): HasMany
    {
        return $this->hasMany(BranchMenu::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getWeekPeriodAttribute(): string
    {
        $start = CarbonImmutable::parse($this->week_start);
        $end = CarbonImmutable::parse($this->week_end);

        if ($start->isSameMonth($end) && $start->year === $end->year) {
            return $start->format('j.').' – '.$end->format('j. n. Y');
        }

        if ($start->year === $end->year) {
            return $start->format('j. n.').' – '.$end->format('j. n. Y');
        }

        return $start->format('j. n. Y').' – '.$end->format('j. n. Y');
    }

    public function isDraft(): bool
    {
        return $this->status === PlannedMenuStatus::Draft;
    }
}
