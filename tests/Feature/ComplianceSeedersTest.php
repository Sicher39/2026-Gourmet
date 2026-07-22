<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\Compliance\LegalBasis;
use App\Enums\Compliance\LegalDocumentType;
use App\Enums\Compliance\ScriptPosition;
use App\Enums\Compliance\TrackingCategory;
use App\Enums\Compliance\TrackingProvider;
use App\Models\CompanyProfile;
use App\Models\CookieSetting;
use App\Models\DataProcessingPurpose;
use App\Models\LegalDocument;
use App\Models\TrackingScript;
use Database\Seeders\CookieSettingSeeder;
use Database\Seeders\DataProcessingPurposeSeeder;
use Database\Seeders\LegalDocumentSeeder;
use Database\Seeders\TrackingScriptSeeder;
use Tests\Concerns\UsesIsolatedTestDatabase;
use Tests\TestCase;

class ComplianceSeedersTest extends TestCase
{
    use UsesIsolatedTestDatabase;

    public function test_data_processing_purposes_are_idempotent(): void
    {
        $this->seed(DataProcessingPurposeSeeder::class);
        $firstCount = DataProcessingPurpose::query()->count();

        $this->seed(DataProcessingPurposeSeeder::class);
        $secondCount = DataProcessingPurpose::query()->count();

        $this->assertSame($firstCount, $secondCount, 'Re-seeding must not create duplicate purposes.');
        $this->assertGreaterThanOrEqual(6, $firstCount, 'Expected at least 6 canonical purposes.');
    }

    public function test_data_processing_purposes_cover_required_contexts(): void
    {
        $this->seed(DataProcessingPurposeSeeder::class);

        $purposes = DataProcessingPurpose::query()->where('is_active', true)->get();

        $names = $purposes->pluck('name')->all();

        $this->assertContains('Zpracování rezervací a plnění smluv', $names);
        $this->assertContains('Komunikace se zákazníky', $names);
        $this->assertContains('Plnění právních a účetních povinností', $names);
        $this->assertContains('Evidence souhlasů a bezpečnostní logy', $names);
        $this->assertContains('Analytika návštěvnosti (pouze se souhlasem)', $names);
        $this->assertContains('Marketingové nástroje (pouze se souhlasem)', $names);
    }

    public function test_data_processing_purposes_have_correct_legal_bases(): void
    {
        $this->seed(DataProcessingPurposeSeeder::class);

        $reservation = DataProcessingPurpose::query()
            ->where('name', 'Zpracování rezervací a plnění smluv')
            ->firstOrFail();
        $this->assertSame(LegalBasis::Contract, $reservation->legal_basis);

        $communication = DataProcessingPurpose::query()
            ->where('name', 'Komunikace se zákazníky')
            ->firstOrFail();
        $this->assertSame(LegalBasis::LegitimateInterest, $communication->legal_basis);

        $legal = DataProcessingPurpose::query()
            ->where('name', 'Plnění právních a účetních povinností')
            ->firstOrFail();
        $this->assertSame(LegalBasis::LegalObligation, $legal->legal_basis);

        $analytics = DataProcessingPurpose::query()
            ->where('name', 'Analytika návštěvnosti (pouze se souhlasem)')
            ->firstOrFail();
        $this->assertSame(LegalBasis::Consent, $analytics->legal_basis);

        $marketing = DataProcessingPurpose::query()
            ->where('name', 'Marketingové nástroje (pouze se souhlasem)')
            ->firstOrFail();
        $this->assertSame(LegalBasis::Consent, $marketing->legal_basis);
    }

    public function test_tracking_scripts_are_idempotent(): void
    {
        $this->seed(TrackingScriptSeeder::class);
        $firstCount = TrackingScript::query()->count();

        $this->seed(TrackingScriptSeeder::class);
        $secondCount = TrackingScript::query()->count();

        $this->assertSame($firstCount, $secondCount, 'Re-seeding must not create duplicate scripts.');
        $this->assertGreaterThanOrEqual(6, $firstCount, 'Expected at least 6 canonical scripts (4 necessary + 2 optional).');
    }

    public function test_legacy_combined_necessary_entry_is_removed(): void
    {
        // Simulate leftover legacy record from a previous seeder version
        TrackingScript::query()->create([
            'name' => 'Session / CSRF / bezpečnostní tokeny',
            'provider' => TrackingProvider::Custom,
            'category' => TrackingCategory::Necessary,
            'position' => ScriptPosition::Head,
            'enabled' => true,
            'requires_consent' => false,
            'priority' => 0,
        ]);

        $this->seed(TrackingScriptSeeder::class);

        $this->assertDatabaseMissing('tracking_scripts', [
            'name' => 'Session / CSRF / bezpečnostní tokeny',
        ]);
    }

    /** @return array<string, array{name: string, provider: TrackingProvider, description: string, provider_name: string|null, provider_privacy_url: string|null, identifier: string|null, priority: int}> */
    public static function necessaryEntriesProvider(): array
    {
        return [
            'Laravel session' => [
                'name' => 'Laravel session',
                'provider' => TrackingProvider::Custom,
                'description' => 'První strana. Uchovává relaci návštěvníka a základní stav webové aplikace. Bez této technické cookie by některé části webu nemusely fungovat správně.',
                'provider_name' => null,
                'provider_privacy_url' => null,
                'identifier' => null,
                'priority' => 10,
            ],
            'CSRF ochrana formulářů' => [
                'name' => 'CSRF ochrana formulářů',
                'provider' => TrackingProvider::Custom,
                'description' => 'První strana. Pomáhá chránit formuláře a požadavky proti zneužití typu CSRF. Jde o bezpečnostní technické opatření.',
                'provider_name' => null,
                'provider_privacy_url' => null,
                'identifier' => null,
                'priority' => 20,
            ],
            'Uložení nastavení cookies' => [
                'name' => 'Uložení nastavení cookies',
                'provider' => TrackingProvider::Custom,
                'description' => 'První strana. Ukládá anonymní identifikátor a nastavení cookies, aby se volba návštěvníka nemusela zobrazovat opakovaně.',
                'provider_name' => null,
                'provider_privacy_url' => null,
                'identifier' => null,
                'priority' => 30,
            ],
            'Adobe Fonts / Typekit' => [
                'name' => 'Adobe Fonts / Typekit',
                'provider' => TrackingProvider::AdobeFonts,
                'description' => 'Externí technická služba pro načtení písma webu z domény use.typekit.net. Může při požadavku zpracovat technické údaje jako IP adresu, user-agent a čas požadavku. Nepoužívá se pro marketingové měření webu.',
                'provider_name' => 'Adobe Fonts',
                'provider_privacy_url' => 'https://www.adobe.com/privacy/policies/adobe-fonts.html',
                'identifier' => 'fkf0aff',
                'priority' => 40,
            ],
        ];
    }

    /**
     * @dataProvider necessaryEntriesProvider
     */
    public function test_necessary_entries_are_correctly_configured(
        string $name,
        TrackingProvider $provider,
        string $description,
        ?string $provider_name,
        ?string $provider_privacy_url,
        ?string $identifier,
        int $priority,
    ): void {
        $this->seed(TrackingScriptSeeder::class);

        $record = TrackingScript::query()->where('name', $name)->first();

        $this->assertNotNull($record, "Necessary record '{$name}' must exist.");
        $this->assertTrue($record->enabled, "Necessary record '{$name}' must be enabled.");
        $this->assertFalse($record->requires_consent, "Necessary record '{$name}' must not require consent.");
        $this->assertSame(TrackingCategory::Necessary, $record->category, "Necessary record '{$name}' must be category Necessary.");
        $this->assertSame($provider, $record->provider, "Necessary record '{$name}' must have correct provider.");
        $this->assertSame($description, $record->description, "Necessary record '{$name}' must have correct description.");
        $this->assertSame($provider_name, $record->provider_name, "Necessary record '{$name}' must have correct provider_name.");
        $this->assertSame($provider_privacy_url, $record->provider_privacy_url, "Necessary record '{$name}' must have correct provider_privacy_url.");
        $this->assertSame($identifier, $record->identifier, "Necessary record '{$name}' must have correct identifier.");
        $this->assertSame($priority, $record->priority, "Necessary record '{$name}' must have correct priority.");
        $this->assertNull($record->code, "Necessary record '{$name}' code must be null.");
    }

    public function test_optional_scripts_are_disabled_and_require_consent(): void
    {
        $this->seed(TrackingScriptSeeder::class);

        $optional = TrackingScript::query()
            ->where('requires_consent', true)
            ->get();

        $this->assertGreaterThanOrEqual(2, $optional->count(), 'Expected at least 2 optional scripts.');

        foreach ($optional as $script) {
            $this->assertFalse(
                $script->enabled,
                "Optional script '{$script->name}' must be disabled by default.",
            );
            $this->assertTrue(
                $script->requires_consent,
                "Optional script '{$script->name}' must require consent.",
            );
            $this->assertContains(
                $script->category->value,
                TrackingCategory::optionalValues(),
                "Optional script '{$script->name}' category must be optional.",
            );
            $this->assertNull(
                $script->identifier,
                "Optional script '{$script->name}' identifier must be null (not configured).",
            );
        }
    }

    public function test_legal_documents_are_idempotent(): void
    {
        $this->seed(LegalDocumentSeeder::class);
        $firstCount = LegalDocument::query()->count();

        $this->seed(LegalDocumentSeeder::class);
        $secondCount = LegalDocument::query()->count();

        $this->assertSame($firstCount, $secondCount, 'Re-seeding must not create duplicate documents.');
        $this->assertGreaterThanOrEqual(2, $firstCount, 'Expected at least 2 canonical documents.');
    }

    public function test_legal_documents_include_privacy_policy_and_cookie_policy(): void
    {
        $this->seed(LegalDocumentSeeder::class);

        $privacy = LegalDocument::query()
            ->where('type', LegalDocumentType::PrivacyPolicy)
            ->where('is_published', true)
            ->first();

        $this->assertNotNull($privacy, 'Published privacy policy must exist.');
        $this->assertNotEmpty($privacy->content, 'Privacy policy must have content.');
        $this->assertNotEmpty($privacy->version, 'Privacy policy must have a version.');
        $this->assertNotNull($privacy->effective_from, 'Privacy policy must have an effective date.');

        $cookie = LegalDocument::query()
            ->where('type', LegalDocumentType::CookiePolicy)
            ->where('is_published', true)
            ->first();

        $this->assertNotNull($cookie, 'Published cookie policy must exist.');
        $this->assertNotEmpty($cookie->content, 'Cookie policy must have content.');
    }

    public function test_legal_document_seeder_reuses_existing_document_type_with_custom_slug(): void
    {
        LegalDocument::query()->create([
            'type' => LegalDocumentType::PrivacyPolicy,
            'title' => 'Původní zásady',
            'slug' => 'vlastni-ochrana-osobnich-udaju',
            'content' => 'Původní obsah',
            'version' => 'custom',
            'is_published' => true,
        ]);

        $this->seed(LegalDocumentSeeder::class);

        $this->assertSame(
            1,
            LegalDocument::query()->where('type', LegalDocumentType::PrivacyPolicy)->count(),
        );
        $this->assertDatabaseHas('legal_documents', [
            'type' => LegalDocumentType::PrivacyPolicy->value,
            'slug' => 'ochrana-osobnich-udaju',
            'version' => '1.0',
        ]);
    }

    public function test_cookie_setting_is_idempotent(): void
    {
        $this->seed(CookieSettingSeeder::class);
        $firstCount = CookieSetting::query()->count();

        $this->seed(CookieSettingSeeder::class);
        $secondCount = CookieSetting::query()->count();

        $this->assertSame($firstCount, $secondCount, 'Re-seeding must not create duplicate cookie settings.');
        $this->assertSame(1, $firstCount, 'Expected exactly 1 cookie-settings record.');
    }

    public function test_cookie_setting_is_enabled_with_baseline_values(): void
    {
        $this->seed(CookieSettingSeeder::class);

        $setting = CookieSetting::query()->firstOrFail();

        $this->assertTrue($setting->enabled, 'Cookie banner must be enabled.');
        $this->assertNotEmpty($setting->banner_title, 'Banner title must not be empty.');
        $this->assertNotEmpty($setting->banner_description, 'Banner description must not be empty.');
        $this->assertNotEmpty($setting->privacy_policy_url, 'Privacy policy URL must not be empty.');
        $this->assertNotEmpty($setting->cookie_policy_url, 'Cookie policy URL must not be empty.');
    }

    public function test_cookie_setting_preserves_admin_configured_urls(): void
    {
        $setting = CookieSetting::query()->create([
            'enabled' => true,
            'version' => '2024-01-01',
            'privacy_policy_url' => '/admin-custom-privacy',
            'cookie_policy_url' => '/admin-custom-cookies',
        ]);

        $this->seed(CookieSettingSeeder::class);

        $setting->refresh();

        $this->assertSame(
            '/admin-custom-privacy',
            $setting->privacy_policy_url,
            'Admin-configured privacy URL must survive re-seed.',
        );
        $this->assertSame(
            '/admin-custom-cookies',
            $setting->cookie_policy_url,
            'Admin-configured cookie URL must survive re-seed.',
        );
    }

    public function test_first_party_necessary_provider_name_is_null_in_database(): void
    {
        $this->seed(TrackingScriptSeeder::class);

        $firstPartyNames = ['Laravel session', 'CSRF ochrana formulářů', 'Uložení nastavení cookies'];

        foreach ($firstPartyNames as $name) {
            $script = TrackingScript::query()->where('name', $name)->first();

            $this->assertNotNull($script, "Script '{$name}' must exist.");
            $this->assertNull(
                $script->provider_name,
                "First-party script '{$name}' provider_name must be null in the database.",
            );
        }
    }

    public function test_third_party_adobe_retains_provider_name(): void
    {
        $this->seed(TrackingScriptSeeder::class);

        $adobe = TrackingScript::query()->where('name', 'Adobe Fonts / Typekit')->first();

        $this->assertNotNull($adobe, 'Adobe Fonts script must exist.');
        $this->assertSame(
            'Adobe Fonts',
            $adobe->provider_name,
            'Adobe Fonts provider_name must remain Adobe Fonts.',
        );
    }

    public function test_cookie_config_api_returns_company_name_for_first_party_entries(): void
    {
        CompanyProfile::query()->create([
            'company_name' => 'U Sajmona pod Hájkem',
        ]);

        $this->seed(TrackingScriptSeeder::class);
        $this->seed(CookieSettingSeeder::class);

        $response = $this->getJson(route('api.compliance.cookie-config'));

        $response->assertOk();

        $scripts = $response->json('trackingScripts');
        $this->assertIsArray($scripts);

        $firstPartyNames = ['Laravel session', 'CSRF ochrana formulářů', 'Uložení nastavení cookies'];

        foreach ($scripts as $script) {
            if (in_array($script['name'], $firstPartyNames, true)) {
                $this->assertSame(
                    'U Sajmona pod Hájkem',
                    $script['providerName'],
                    "First-party script '{$script['name']}' providerName must resolve to company name.",
                );
            }

            if ($script['name'] === 'Adobe Fonts / Typekit') {
                $this->assertSame(
                    'Adobe Fonts',
                    $script['providerName'],
                    'Adobe Fonts providerName must remain Adobe Fonts.',
                );
            }
        }
    }

    public function test_gdpr_inertia_returns_company_name_for_technical_cookies(): void
    {
        CompanyProfile::query()->create([
            'company_name' => 'U Sajmona pod Hájkem',
        ]);

        $this->seed(TrackingScriptSeeder::class);
        $this->seed(CookieSettingSeeder::class);

        $response = $this->get(route('front.gdpr'));

        $response->assertOk();

        $props = $response->inertiaProps();
        $this->assertArrayHasKey('technicalCookies', $props);

        $technicalCookies = $props['technicalCookies'];
        $this->assertIsArray($technicalCookies);

        $firstPartyNames = ['Laravel session', 'CSRF ochrana formulářů', 'Uložení nastavení cookies'];

        foreach ($technicalCookies as $cookie) {
            if (in_array($cookie['name'], $firstPartyNames, true)) {
                $this->assertSame(
                    'U Sajmona pod Hájkem',
                    $cookie['providerName'],
                    "First-party technical cookie '{$cookie['name']}' providerName must resolve to company name.",
                );
            }

            if ($cookie['name'] === 'Adobe Fonts / Typekit') {
                $this->assertSame(
                    'Adobe Fonts',
                    $cookie['providerName'],
                    'Adobe Fonts providerName must remain Adobe Fonts.',
                );
            }
        }
    }

    public function test_cookie_config_api_without_company_profile_returns_null_for_first_party(): void
    {
        $this->seed(TrackingScriptSeeder::class);
        $this->seed(CookieSettingSeeder::class);

        $response = $this->getJson(route('api.compliance.cookie-config'));

        $response->assertOk();

        $scripts = $response->json('trackingScripts');
        $this->assertIsArray($scripts);

        $firstPartyNames = ['Laravel session', 'CSRF ochrana formulářů', 'Uložení nastavení cookies'];

        foreach ($scripts as $script) {
            if (in_array($script['name'], $firstPartyNames, true)) {
                $this->assertNull(
                    $script['providerName'],
                    "First-party script '{$script['name']}' providerName must be null when no CompanyProfile exists.",
                );
            }
        }
    }

    public function test_gdpr_inertia_without_company_profile_returns_null_for_technical_cookies(): void
    {
        $this->seed(TrackingScriptSeeder::class);
        $this->seed(CookieSettingSeeder::class);

        $response = $this->get(route('front.gdpr'));

        $response->assertOk();

        $props = $response->inertiaProps();
        $this->assertArrayHasKey('technicalCookies', $props);

        $technicalCookies = $props['technicalCookies'];
        $this->assertIsArray($technicalCookies);

        $firstPartyNames = ['Laravel session', 'CSRF ochrana formulářů', 'Uložení nastavení cookies'];

        foreach ($technicalCookies as $cookie) {
            if (in_array($cookie['name'], $firstPartyNames, true)) {
                $this->assertNull(
                    $cookie['providerName'],
                    "First-party technical cookie '{$cookie['name']}' providerName must be null when no CompanyProfile exists.",
                );
            }
        }
    }

    public function test_cookie_config_api_does_not_persist_company_name_to_database(): void
    {
        CompanyProfile::query()->create([
            'company_name' => 'U Sajmona pod Hájkem',
        ]);

        $this->seed(TrackingScriptSeeder::class);
        $this->seed(CookieSettingSeeder::class);

        // Verify DB is null before calling the API
        foreach (['Laravel session', 'CSRF ochrana formulářů', 'Uložení nastavení cookies'] as $name) {
            $this->assertNull(
                TrackingScript::query()->where('name', $name)->value('provider_name'),
                "First-party script '{$name}' provider_name must be null in DB before API call.",
            );
        }

        // Hit the API — this resolves company name at presentation time
        $response = $this->getJson(route('api.compliance.cookie-config'));
        $response->assertOk();

        // Confirm the database was NOT mutated
        foreach (['Laravel session', 'CSRF ochrana formulářů', 'Uložení nastavení cookies'] as $name) {
            $this->assertNull(
                TrackingScript::query()->where('name', $name)->value('provider_name'),
                "First-party script '{$name}' provider_name must still be null in DB after API call.",
            );
        }
    }

    public function test_gdpr_inertia_does_not_persist_company_name_to_database(): void
    {
        CompanyProfile::query()->create([
            'company_name' => 'U Sajmona pod Hájkem',
        ]);

        $this->seed(TrackingScriptSeeder::class);
        $this->seed(CookieSettingSeeder::class);

        // Verify DB is null before calling the page
        foreach (['Laravel session', 'CSRF ochrana formulářů', 'Uložení nastavení cookies'] as $name) {
            $this->assertNull(
                TrackingScript::query()->where('name', $name)->value('provider_name'),
                "First-party script '{$name}' provider_name must be null in DB before GDPR page load.",
            );
        }

        // Hit the GDPR Inertia page
        $response = $this->get(route('front.gdpr'));
        $response->assertOk();

        // Confirm the database was NOT mutated
        foreach (['Laravel session', 'CSRF ochrana formulářů', 'Uložení nastavení cookies'] as $name) {
            $this->assertNull(
                TrackingScript::query()->where('name', $name)->value('provider_name'),
                "First-party script '{$name}' provider_name must still be null in DB after GDPR page load.",
            );
        }
    }

    public function test_third_party_ga4_and_meta_retain_provider_names(): void
    {
        $this->seed(TrackingScriptSeeder::class);

        $ga4 = TrackingScript::query()->where('name', 'Google Analytics 4')->first();
        $this->assertNotNull($ga4, 'GA4 script must exist.');
        $this->assertSame(
            'Google Ireland Limited',
            $ga4->provider_name,
            'GA4 provider_name must remain Google Ireland Limited.',
        );
        $this->assertFalse($ga4->enabled, 'GA4 must remain disabled by default.');

        $meta = TrackingScript::query()->where('name', 'Meta Pixel')->first();
        $this->assertNotNull($meta, 'Meta Pixel script must exist.');
        $this->assertSame(
            'Meta Platforms Ireland Limited',
            $meta->provider_name,
            'Meta Pixel provider_name must remain Meta Platforms Ireland Limited.',
        );
        $this->assertFalse($meta->enabled, 'Meta Pixel must remain disabled by default.');
    }
}
