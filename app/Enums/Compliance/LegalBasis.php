<?php

declare(strict_types=1);

namespace App\Enums\Compliance;

enum LegalBasis: string
{
    case Contract = 'contract';
    case LegalObligation = 'legal_obligation';
    case LegitimateInterest = 'legitimate_interest';
    case Consent = 'consent';

    public function label(): string
    {
        return match ($this) {
            self::Contract => 'Plnění smlouvy',
            self::LegalObligation => 'Právní povinnost',
            self::LegitimateInterest => 'Oprávněný zájem',
            self::Consent => 'Souhlas',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $case): array => [$case->value => $case->label()])->all();
    }
}
