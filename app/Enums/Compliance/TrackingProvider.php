<?php

declare(strict_types=1);

namespace App\Enums\Compliance;

enum TrackingProvider: string
{
    case Ga4 = 'ga4';
    case GoogleAds = 'google_ads';
    case GoogleTagManager = 'google_tag_manager';
    case MetaPixel = 'meta_pixel';
    case Sklik = 'sklik';
    case Clarity = 'clarity';
    case Hotjar = 'hotjar';
    case AdobeFonts = 'adobe_fonts';
    case Custom = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::Ga4 => 'Google Analytics 4',
            self::GoogleAds => 'Google Ads',
            self::GoogleTagManager => 'Google Tag Manager',
            self::MetaPixel => 'Meta Pixel',
            self::Sklik => 'Sklik / Seznam',
            self::Clarity => 'Microsoft Clarity',
            self::Hotjar => 'Hotjar',
            self::AdobeFonts => 'Adobe Fonts',
            self::Custom => 'Vlastní script',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $case): array => [$case->value => $case->label()])->all();
    }
}
