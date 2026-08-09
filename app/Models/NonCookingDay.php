<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PlannedMenuStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NonCookingDay extends Model
{
    protected static function booted(): void
    {
        static::saved(function (NonCookingDay $day): void {
            if ($day->wasChanged('date')) {
                static::markPlannedDays((string) $day->getOriginal('date'), false);
            }

            static::markPlannedDays($day->date->toDateString(), true);
        });

        static::deleted(fn (NonCookingDay $day) => static::markPlannedDays($day->date->toDateString(), false));
    }

    private static function markPlannedDays(string $date, bool $isNonCookingDay): void
    {
        PlannedMenuDay::query()
            ->whereDate('date', $date)
            ->whereHas('plannedMenu', fn ($query) => $query->where('status', PlannedMenuStatus::Draft->value))
            ->update(['is_non_cooking_day' => $isNonCookingDay]);
    }

    protected $fillable = ['date', 'internal_note', 'created_by'];

    protected function casts(): array
    {
        return ['date' => 'date'];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
