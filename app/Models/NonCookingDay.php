<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PlannedMenuStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

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
        $plannedDays = PlannedMenuDay::query()
            ->whereDate('date', $date)
            ->whereHas('plannedMenu', fn ($query) => $query->where('status', PlannedMenuStatus::Draft->value));

        if ($isNonCookingDay) {
            DB::table('planned_menu_common_item_days')
                ->whereIn('planned_menu_day_id', $plannedDays->clone()->select('id'))
                ->delete();
        }

        $plannedDays->update(['is_non_cooking_day' => $isNonCookingDay]);
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
