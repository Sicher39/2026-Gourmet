<?php

declare(strict_types=1);

namespace App\Enums;

enum BranchMenuStatus: string
{
    case Ready = 'ready';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Ready => 'Připravený',
            self::Closed => 'Ukončený',
        };
    }
}
