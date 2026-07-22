<?php

declare(strict_types=1);

namespace App\Enums\Compliance;

enum ConsentType: string
{
    case Cookie = 'cookie';
    case Marketing = 'marketing';
    case Newsletter = 'newsletter';
    case Gdpr = 'gdpr';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Cookie => 'Cookies',
            self::Marketing => 'Marketing',
            self::Newsletter => 'Newsletter',
            self::Gdpr => 'GDPR',
            self::Other => 'Jiné',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $case): array => [$case->value => $case->label()])->all();
    }
}
