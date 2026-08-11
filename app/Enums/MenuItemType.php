<?php

declare(strict_types=1);

namespace App\Enums;

enum MenuItemType: string
{
    case Soup = 'soup';
    case Main = 'main';
    case Pizza = 'pizza';
    case Grill = 'grill';

    public function label(): string
    {
        return match ($this) {
            self::Soup => 'Polévka',
            self::Main => 'Menu',
            self::Pizza => 'Pizza',
            self::Grill => 'Grill',
        };
    }
}
