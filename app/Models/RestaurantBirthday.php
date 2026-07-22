<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class RestaurantBirthday extends Model
{
    public const SingletonKey = 'default';

    protected $table = 'restaurant_birthdays';

    protected $fillable = [
        'celebration_month',
        'celebration_day',
        'celebration_time',
        'celebration_at',
    ];

    protected static function booted(): void
    {
        static::creating(function (RestaurantBirthday $birthday): void {
            if (Schema::hasColumn($birthday->getTable(), 'singleton_key')) {
                $birthday->singleton_key = self::SingletonKey;
            }
        });

        static::saving(function (RestaurantBirthday $birthday): void {
            if (! Schema::hasColumn($birthday->getTable(), 'celebration_at')) {
                return;
            }

            $month = $birthday->annualMonth();
            $day = $birthday->annualDay();
            $time = $birthday->annualTime();

            if ($month === null || $day === null || $time === null) {
                return;
            }

            $birthday->celebration_at = sprintf('%d-%02d-%02d %s', now()->year, $month, $day, $time);
        });
    }

    public static function current(): ?self
    {
        $model = new self;

        if (! Schema::hasTable($model->getTable())) {
            return null;
        }

        $query = self::query();

        if (Schema::hasColumn($model->getTable(), 'singleton_key')) {
            $query->where('singleton_key', self::SingletonKey);
        }

        if (Schema::hasColumn($model->getTable(), 'deleted_at')) {
            $query->whereNull('deleted_at');
        }

        return $query->first();
    }

    public function annualMonth(): ?int
    {
        if ($this->celebration_month !== null) {
            return (int) $this->celebration_month;
        }

        return $this->celebration_at instanceof CarbonInterface
            ? $this->celebration_at->month
            : null;
    }

    public function annualDay(): ?int
    {
        if ($this->celebration_day !== null) {
            return (int) $this->celebration_day;
        }

        return $this->celebration_at instanceof CarbonInterface
            ? $this->celebration_at->day
            : null;
    }

    public function annualTime(): ?string
    {
        if (filled($this->celebration_time)) {
            return substr((string) $this->celebration_time, 0, 5);
        }

        return $this->celebration_at instanceof CarbonInterface
            ? $this->celebration_at->format('H:i')
            : null;
    }

    /**
     * @return array{month: int, day: int, time: string}|null
     */
    public function annualDatePayload(): ?array
    {
        $month = $this->annualMonth();
        $day = $this->annualDay();
        $time = $this->annualTime();

        if ($month === null || $day === null || $time === null) {
            return null;
        }

        return [
            'month' => $month,
            'day' => $day,
            'time' => $time,
        ];
    }

    protected function casts(): array
    {
        return [
            'celebration_month' => 'integer',
            'celebration_day' => 'integer',
            'celebration_at' => 'datetime',
        ];
    }
}
