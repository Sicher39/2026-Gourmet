<?php

declare(strict_types=1);

namespace App\Enums;

enum PlannedMenuStatus: string
{
    case Draft = 'draft';
    case Approved = 'approved';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Rozpracovaný',
            self::Approved => 'Odsouhlasený',
        };
    }
}
