<?php

declare(strict_types=1);

namespace App\Enums\Compliance;

enum ScriptPosition: string
{
    case Head = 'head';
    case BodyStart = 'body_start';
    case BodyEnd = 'body_end';

    public function label(): string
    {
        return match ($this) {
            self::Head => 'Hlavička',
            self::BodyStart => 'Začátek body',
            self::BodyEnd => 'Konec body',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $case): array => [$case->value => $case->label()])->all();
    }
}
