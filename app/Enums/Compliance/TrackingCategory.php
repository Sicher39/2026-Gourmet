<?php

declare(strict_types=1);

namespace App\Enums\Compliance;

enum TrackingCategory: string
{
    case Necessary = 'necessary';
    case Analytics = 'analytics';
    case Marketing = 'marketing';
    case Preferences = 'preferences';

    public function label(): string
    {
        return match ($this) {
            self::Necessary => 'Nezbytné',
            self::Analytics => 'Analytické',
            self::Marketing => 'Marketingové',
            self::Preferences => 'Preferenční',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $case): array => [$case->value => $case->label()])->all();
    }

    public static function optionalValues(): array
    {
        return [self::Analytics->value, self::Marketing->value, self::Preferences->value];
    }
}
