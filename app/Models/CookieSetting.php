<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CookieSetting extends Model
{
    protected $fillable = [
        'enabled',
        'version',
        'banner_title',
        'banner_description',
        'accept_all_label',
        'reject_all_label',
        'customize_label',
        'save_preferences_label',
        'necessary_title',
        'necessary_description',
        'analytics_title',
        'analytics_description',
        'marketing_title',
        'marketing_description',
        'preferences_title',
        'preferences_description',
        'footer_link_label',
        'privacy_policy_url',
        'cookie_policy_url',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    public static function current(): self
    {
        return self::query()->first() ?? self::query()->create(self::defaults());
    }

    public static function defaults(): array
    {
        return [
            'enabled' => true,
            'version' => now()->toDateString(),
            'banner_title' => 'Nastavení cookies',
            'banner_description' => 'Používáme nezbytné technické cookies pro správné fungování webu. Analytické a marketingové nástroje spustíme pouze po vašem souhlasu.',
            'accept_all_label' => 'Povolit vše',
            'reject_all_label' => 'Odmítnout vše',
            'customize_label' => 'Nastavení cookies',
            'save_preferences_label' => 'Uložit nastavení',
            'necessary_title' => 'Nezbytné cookies',
            'necessary_description' => 'Tyto cookies jsou nutné pro fungování webu a nelze je vypnout.',
            'analytics_title' => 'Analytické cookies',
            'analytics_description' => 'Pomáhají nám rozumět návštěvnosti a zlepšovat web.',
            'marketing_title' => 'Marketingové cookies',
            'marketing_description' => 'Umožňují měření a personalizaci reklamních kampaní.',
            'preferences_title' => 'Preferenční cookies',
            'preferences_description' => 'Pomáhají uchovat volby a nastavení návštěvníka.',
            'footer_link_label' => 'Nastavení cookies',
            'privacy_policy_url' => '/ochrana-osobnich-udaju',
            'cookie_policy_url' => '/zasady-cookies',
        ];
    }
}
