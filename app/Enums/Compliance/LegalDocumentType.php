<?php

declare(strict_types=1);

namespace App\Enums\Compliance;

enum LegalDocumentType: string
{
    case PrivacyPolicy = 'privacy_policy';
    case CookiePolicy = 'cookie_policy';
    case TermsAndConditions = 'terms_and_conditions';
    case WithdrawalPolicy = 'withdrawal_policy';
    case ComplaintsPolicy = 'complaints_policy';
    case Custom = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::PrivacyPolicy => 'Ochrana osobních údajů',
            self::CookiePolicy => 'Zásady cookies',
            self::TermsAndConditions => 'Obchodní podmínky',
            self::WithdrawalPolicy => 'Odstoupení od smlouvy',
            self::ComplaintsPolicy => 'Reklamační řád',
            self::Custom => 'Vlastní dokument',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $case): array => [$case->value => $case->label()])->all();
    }
}
