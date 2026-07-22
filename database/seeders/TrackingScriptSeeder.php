<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Compliance\ScriptPosition;
use App\Enums\Compliance\TrackingCategory;
use App\Enums\Compliance\TrackingProvider;
use App\Models\TrackingScript;
use Illuminate\Database\Seeder;

class TrackingScriptSeeder extends Seeder
{
    /**
     * Name of the legacy combined necessary entry that is replaced by
     * the four granular entries below.  It is deleted before upsert so
     * that repeated seed runs do not leave a stale record.
     */
    private const LEGACY_ENTRY_NAME = 'Session / CSRF / bezpečnostní tokeny';

    /**
     * Idempotently seed canonical tracking-script templates.
     *
     * The `name` column is used as the idempotency key because it is
     * the most stable human-readable identifier.  Admin-created scripts
     * with different names are left untouched.
     *
     * ── Necessary entries ──
     * Four granular necessary entries replace the legacy combined record.
     * All are enabled, require no consent, have no executable code, and
     * use deterministic priorities 10-40.  First-party entries use the
     * Custom provider; Adobe Fonts uses the dedicated AdobeFonts provider
     * with its official privacy URL.  The Adobe Fonts stylesheet itself
     * is loaded by app.blade.php; this entry only documents the cookie
     * for transparency on the cookie-policy page.
     *
     * ── Optional provider templates ──
     * Disabled by default with null/blank identifiers so that no
     * external tracking can fire before an administrator explicitly
     * configures them.  Each template requires consent and carries
     * the provider's privacy URL for transparency.
     */
    private const SCRIPTS = [
        [
            'name' => 'Laravel session',
            'provider' => TrackingProvider::Custom,
            'category' => TrackingCategory::Necessary,
            'position' => ScriptPosition::Head,
            'identifier' => null,
            'code' => null,
            'description' => 'První strana. Uchovává relaci návštěvníka a základní stav webové aplikace. Bez této technické cookie by některé části webu nemusely fungovat správně.',
            'provider_name' => null,
            'provider_privacy_url' => null,
            'enabled' => true,
            'requires_consent' => false,
            'priority' => 10,
            'only_paths' => null,
            'except_paths' => null,
        ],
        [
            'name' => 'CSRF ochrana formulářů',
            'provider' => TrackingProvider::Custom,
            'category' => TrackingCategory::Necessary,
            'position' => ScriptPosition::Head,
            'identifier' => null,
            'code' => null,
            'description' => 'První strana. Pomáhá chránit formuláře a požadavky proti zneužití typu CSRF. Jde o bezpečnostní technické opatření.',
            'provider_name' => null,
            'provider_privacy_url' => null,
            'enabled' => true,
            'requires_consent' => false,
            'priority' => 20,
            'only_paths' => null,
            'except_paths' => null,
        ],
        [
            'name' => 'Uložení nastavení cookies',
            'provider' => TrackingProvider::Custom,
            'category' => TrackingCategory::Necessary,
            'position' => ScriptPosition::Head,
            'identifier' => null,
            'code' => null,
            'description' => 'První strana. Ukládá anonymní identifikátor a nastavení cookies, aby se volba návštěvníka nemusela zobrazovat opakovaně.',
            'provider_name' => null,
            'provider_privacy_url' => null,
            'enabled' => true,
            'requires_consent' => false,
            'priority' => 30,
            'only_paths' => null,
            'except_paths' => null,
        ],
        [
            'name' => 'Adobe Fonts / Typekit',
            'provider' => TrackingProvider::AdobeFonts,
            'category' => TrackingCategory::Necessary,
            'position' => ScriptPosition::Head,
            'identifier' => 'fkf0aff',
            'code' => null,
            'description' => 'Externí technická služba pro načtení písma webu z domény use.typekit.net. Může při požadavku zpracovat technické údaje jako IP adresu, user-agent a čas požadavku. Nepoužívá se pro marketingové měření webu.',
            'provider_name' => 'Adobe Fonts',
            'provider_privacy_url' => 'https://www.adobe.com/privacy/policies/adobe-fonts.html',
            'enabled' => true,
            'requires_consent' => false,
            'priority' => 40,
            'only_paths' => null,
            'except_paths' => null,
        ],
        [
            'name' => 'Google Analytics 4',
            'provider' => TrackingProvider::Ga4,
            'category' => TrackingCategory::Analytics,
            'position' => ScriptPosition::Head,
            'identifier' => null,
            'code' => null,
            'description' => 'Služba Google Analytics 4 slouží k měření a analýze návštěvnosti webu. Aktivuje se pouze po udělení souhlasu v cookie liště. Před nasazením je nutné zadat měřicí ID ve Filament administraci.',
            'provider_name' => 'Google Ireland Limited',
            'provider_privacy_url' => 'https://policies.google.com/privacy',
            'enabled' => false,
            'requires_consent' => true,
            'priority' => 50,
            'only_paths' => null,
            'except_paths' => null,
        ],
        [
            'name' => 'Meta Pixel',
            'provider' => TrackingProvider::MetaPixel,
            'category' => TrackingCategory::Marketing,
            'position' => ScriptPosition::Head,
            'identifier' => null,
            'code' => null,
            'description' => 'Meta Pixel slouží k měření konverzí a efektivity reklamních kampaní na platformách Meta (Facebook, Instagram). Aktivuje se pouze po udělení souhlasu v cookie liště. Před nasazením je nutné zadat Pixel ID ve Filament administraci.',
            'provider_name' => 'Meta Platforms Ireland Limited',
            'provider_privacy_url' => 'https://www.facebook.com/privacy/policy',
            'enabled' => false,
            'requires_consent' => true,
            'priority' => 60,
            'only_paths' => null,
            'except_paths' => null,
        ],
    ];

    public function run(): void
    {
        // Remove the legacy combined entry so it does not coexist with
        // the four new granular necessary entries.  This targets only
        // the exact seed-owned name; admin-created records are untouched.
        TrackingScript::query()->where('name', self::LEGACY_ENTRY_NAME)->delete();

        foreach (self::SCRIPTS as $script) {
            TrackingScript::query()->updateOrCreate(
                ['name' => $script['name']],
                $script,
            );
        }
    }
}
