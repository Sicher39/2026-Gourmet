<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CookieSetting;
use App\Models\TrackingScript;
use Illuminate\Database\Seeder;

class CookieSettingSeeder extends Seeder
{
    /**
     * Idempotently seed the canonical cookie-setting configuration.
     *
     * Only one active cookie-settings record is expected; the seeder
     * targets the first record (the same one CookieSetting::current()
     * returns).  It applies canonical defaults for banner labels and
     * category descriptions, but preserves any admin-configured URLs
     * when they differ from the default paths so that customised
     * routing is not silently reverted.
     *
     * The version field is bumped to today's date only when the record
     * is newly created; existing records keep their version so that
     * consent-version logic is not invalidated by a seeder re-run.
     */
    public function run(): void
    {
        $defaults = CookieSetting::defaults();

        $setting = CookieSetting::query()->first();

        if ($setting === null) {
            CookieSetting::query()->create($defaults);

            return;
        }

        $labels = [
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
        ];

        foreach ($labels as $field) {
            $setting->{$field} = $defaults[$field];
        }

        $setting->necessary_description = $defaults['necessary_description'];

        $optionalScriptCategories = TrackingScript::categoryCountsForBanner();
        $setting->banner_description = $this->buildBannerDescription(
            $defaults['banner_description'],
            $optionalScriptCategories
        );

        $defaultPrivacyUrl = $defaults['privacy_policy_url'];
        $defaultCookieUrl = $defaults['cookie_policy_url'];

        if ($setting->privacy_policy_url === null || $setting->privacy_policy_url === '') {
            $setting->privacy_policy_url = $defaultPrivacyUrl;
        }

        if ($setting->cookie_policy_url === null || $setting->cookie_policy_url === '') {
            $setting->cookie_policy_url = $defaultCookieUrl;
        }

        $setting->enabled = true;

        $setting->save();
    }

    private function buildBannerDescription(string $base, ?array $counts): string
    {
        if ($counts === null || empty($counts)) {
            return $base;
        }

        $parts = [];

        if (((int) ($counts['analytics'] ?? 0)) > 0) {
            $parts[] = 'analytických ('.((int) $counts['analytics']).')';
        }

        if (((int) ($counts['marketing'] ?? 0)) > 0) {
            $parts[] = 'marketingových ('.((int) $counts['marketing']).')';
        }

        if (((int) ($counts['preferences'] ?? 0)) > 0) {
            $parts[] = 'preferenčních ('.((int) $counts['preferences']).')';
        }

        if (empty($parts)) {
            return $base;
        }

        return $base.' Další kategorie využíváme na základě vašeho souhlasu: '
            .implode(', ', $parts).'.';
    }
}
