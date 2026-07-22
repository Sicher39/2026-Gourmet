<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum MenuCatalogKind: string implements HasLabel
{
    case Food = 'food';
    case Beverage = 'beverage';

    public function getLabel(): string
    {
        return match ($this) {
            self::Food => 'Jídelní lístek',
            self::Beverage => 'Nápojový lístek',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $kind): array => [$kind->value => $kind->getLabel()])
            ->all();
    }
}
