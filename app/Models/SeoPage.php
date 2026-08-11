<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class SeoPage extends Model
{
    public const KEY_GLOBAL = 'global';

    private const DEFAULT_SITE_NAME = 'Gourmet Restaurant';

    private const DEFAULT_TITLE = 'Gourmet Restaurant Brno | Ponávka a U Vaňkovky';

    private const DEFAULT_DESCRIPTION = 'Gourmet Restaurant v Brně: polední menu, kavárna, rozvoz, catering a rauty. Navštivte nás na Ponávce nebo U Vaňkovky.';

    private const DEFAULT_ROBOTS = 'index, follow';

    private const DEFAULT_OG_LOCALE = 'cs_CZ';

    private const DEFAULT_TWITTER_CARD = 'summary_large_image';

    private const DEFAULT_SCHEMA_CONTEXT = 'https://schema.org';

    private const DEFAULT_OG_IMAGE_PATH = '/img/logo/gourmet-logo.svg';

    protected $fillable = [
        'key',
        'page_name',
        'route_name',
        'path',
        'is_global',
        'is_active',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'canonical_url',
        'robots',
        'og_title',
        'og_description',
        'og_image',
        'twitter_title',
        'twitter_description',
        'twitter_image',
        'social_facebook_url',
        'social_instagram_url',
        'social_linkedin_url',
        'social_youtube_url',
        'business_name',
        'street_address',
        'address_locality',
        'postal_code',
        'address_country',
        'area_served',
        'available_languages',
        'latitude',
        'longitude',
        'offers_online',
        'schema_type',
        'schema_json',
        'aeo_summary',
        'aeo_faq',
        'aeo_entities',
        'aeo_search_intent',
        'notes',
    ];

    protected $casts = [
        'is_global' => 'bool',
        'is_active' => 'bool',
        'seo_keywords' => 'array',
        'schema_json' => 'array',
        'aeo_faq' => 'array',
        'aeo_entities' => 'array',
        'area_served' => 'array',
        'available_languages' => 'array',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'offers_online' => 'bool',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $seoPage): void {
            if ($seoPage->exists && (bool) $seoPage->getOriginal('is_global')) {
                $seoPage->is_global = true;
                $seoPage->key = (string) ($seoPage->getOriginal('key') ?? self::KEY_GLOBAL);
            }

            if (! $seoPage->is_global) {
                return;
            }

            $globalRecordsQuery = static::query()->where('is_global', true);

            if ($seoPage->exists) {
                $globalRecordsQuery->whereKeyNot($seoPage->getKey());
            }

            $globalRecordsQuery->update(['is_global' => false]);
        });

        static::deleting(function (self $seoPage): void {
            if (! $seoPage->is_global) {
                return;
            }

            throw ValidationException::withMessages([
                'seo_page' => 'Globální fallback nelze smazat.',
            ]);
        });
    }

    /**
     * @return array<string, mixed>
     */
    public static function resolveForRequest(Request $request): array
    {
        $routeName = $request->route()?->getName();
        $path = self::normalizePath($request->path());
        $currentUrl = $request->url();

        return self::resolve(routeName: $routeName, path: $path, currentUrl: $currentUrl);
    }

    /**
     * @return array<string, mixed>
     */
    public static function resolve(?string $routeName = null, ?string $path = null, ?string $currentUrl = null): array
    {
        $normalizedPath = self::normalizePath($path);

        if (! Schema::hasTable('seo_pages')) {
            return self::fallbackSeo($normalizedPath, $currentUrl);
        }

        $globalSeo = static::query()
            ->where('is_active', true)
            ->where(fn ($query) => $query
                ->where('is_global', true)
                ->orWhere('key', self::KEY_GLOBAL))
            ->orderByDesc('is_global')
            ->orderBy('id')
            ->first();

        $pageSeo = null;

        if ($routeName !== null && $routeName !== '') {
            $pageSeo = static::query()
                ->where('is_active', true)
                ->where('route_name', $routeName)
                ->first();
        }

        if ($pageSeo === null && $normalizedPath !== null) {
            $pageSeo = static::query()
                ->where('is_active', true)
                ->where('path', $normalizedPath)
                ->first();
        }

        return self::buildResolvedSeo($globalSeo, $pageSeo, $normalizedPath, $currentUrl);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function defaultRecords(): array
    {
        $records = collect(self::frontPageDefinitions())
            ->map(fn (array $pageDefinition): array => self::frontPageRecord(
                key: $pageDefinition['key'],
                pageName: $pageDefinition['page_name'],
                routeName: $pageDefinition['route_name'],
                path: $pageDefinition['path'],
                schemaType: $pageDefinition['schema_type'],
                overrides: $pageDefinition['overrides'] ?? [],
            ))
            ->all();

        array_unshift($records, self::globalRecord());

        return $records;
    }

    /**
     * @return array<int, array{key: string, page_name: string, route_name: string, path: string, schema_type: string, overrides?: array<string, mixed>}>
     */
    public static function frontPageDefinitions(): array
    {
        return [
            [
                'key' => 'front-index',
                'page_name' => 'Gourmet Restaurant Brno',
                'route_name' => 'front.index',
                'path' => '/',
                'schema_type' => 'WebSite',
                'overrides' => [
                    'seo_title' => 'Gourmet Restaurant Brno | Ponávka a U Vaňkovky',
                    'seo_description' => 'Gourmet Restaurant v Brně: polední menu, kavárna, rozvoz, catering a rauty. Navštivte pobočku Ponávka nebo U Vaňkovky.',
                    'seo_keywords' => ['Gourmet Restaurant Brno', 'restaurace Brno', 'polední menu Brno', 'catering Brno', 'rauty Brno', 'rozvoz jídla Brno', 'Ponávka', 'U Vaňkovky'],
                    'business_name' => 'Gourmet Restaurant',
                    'area_served' => ['Brno', 'Brno-střed', 'Trnitá', 'Ponávka'],
                    'available_languages' => ['cs'],
                    'offers_online' => true,
                    'aeo_summary' => 'Gourmet Restaurant provozuje dvě pobočky v Brně – na Ponávce a U Vaňkovky. Nabízí polední menu, kavárnu, rozvoz, catering a rauty.',
                    'aeo_search_intent' => 'Lokální a komerční – hledání restaurace, poledního menu, cateringu nebo rozvozu v Brně.',
                    'aeo_entities' => ['Gourmet Restaurant', 'Brno', 'Ponávka', 'U Vaňkovky', 'catering', 'rauty', 'rozvoz'],
                    'aeo_faq' => [
                        ['question' => 'Kde najdu Gourmet Restaurant v Brně?', 'answer' => 'Gourmet Restaurant má pobočky na Ponávce a U Vaňkovky. Aktuální kontakty a otevírací dobu najdete u jednotlivých poboček.'],
                        ['question' => 'Nabízí Gourmet Restaurant catering a rauty?', 'answer' => 'Ano. Gourmet připravuje catering a rauty pro firemní i soukromé akce; poptávku můžete zaslat přes uvedené kontakty.'],
                        ['question' => 'Má Gourmet Restaurant rozvoz?', 'answer' => 'Aktuální možnosti rozvozu a dostupné rozvozové služby jsou uvedeny na hlavní stránce.'],
                    ],
                ],
            ],
            [
                'key' => 'front-ponavka',
                'page_name' => 'Gourmet Ponávka',
                'route_name' => 'front.ponavka-branch',
                'path' => '/gourmet-ponavka',
                'schema_type' => 'Restaurant',
                'overrides' => [
                    'seo_title' => 'Gourmet Ponávka | Polední menu a kavárna v Brně',
                    'seo_description' => 'Gourmet Ponávka na Škrobárenské v Brně: polední menu, kavárna a rozvoz. Zjistěte aktuální nabídku, otevírací dobu a kontakt.',
                    'seo_keywords' => ['Gourmet Ponávka', 'restaurace Ponávka', 'polední menu Ponávka', 'polední menu Škrobárenská', 'kavárna Ponávka', 'rozvoz Ponávka'],
                    'business_name' => 'Gourmet Ponávka',
                    'street_address' => 'Škrobárenská 511/3',
                    'address_locality' => 'Brno – Trnitá',
                    'postal_code' => '617 00',
                    'address_country' => 'CZ',
                    'area_served' => ['Brno', 'Trnitá', 'Ponávka'],
                    'available_languages' => ['cs'],
                    'offers_online' => true,
                    'aeo_summary' => 'Gourmet Ponávka je restaurace a kavárna na Škrobárenské v Brně. Nabízí polední menu a rozvoz podle aktuální nabídky.',
                    'aeo_search_intent' => 'Lokální – hledání poledního menu, kavárny, rozvozu a kontaktu na Ponávce.',
                    'aeo_entities' => ['Gourmet Ponávka', 'Škrobárenská 511/3', 'Brno – Trnitá', 'polední menu', 'kavárna'],
                ],
            ],
            [
                'key' => 'front-vankovka',
                'page_name' => 'Gourmet U Vaňkovky',
                'route_name' => 'front.vankovka-branch',
                'path' => '/gourmet-u-vankovky',
                'schema_type' => 'Restaurant',
                'overrides' => [
                    'seo_title' => 'Gourmet U Vaňkovky | Polední menu a kavárna v Brně',
                    'seo_description' => 'Gourmet U Vaňkovky v Brně: polední menu a kavárna v blízkosti centra. Zjistěte aktuální nabídku, otevírací dobu a kontakt.',
                    'seo_keywords' => ['Gourmet U Vaňkovky', 'restaurace U Vaňkovky', 'polední menu Vaňkovka', 'polední menu Brno-střed', 'kavárna Vaňkovka'],
                    'business_name' => 'Gourmet U Vaňkovky',
                    'street_address' => 'Trnitá 500/9',
                    'address_locality' => 'Brno-střed',
                    'postal_code' => '602 00',
                    'address_country' => 'CZ',
                    'area_served' => ['Brno', 'Brno-střed', 'U Vaňkovky'],
                    'available_languages' => ['cs'],
                    'aeo_summary' => 'Gourmet U Vaňkovky je restaurace a kavárna v Brně-střed na ulici Trnitá. Nabízí polední menu podle aktuální nabídky.',
                    'aeo_search_intent' => 'Lokální – hledání poledního menu, kavárny a kontaktu v oblasti U Vaňkovky.',
                    'aeo_entities' => ['Gourmet U Vaňkovky', 'Trnitá 500/9', 'Brno-střed', 'polední menu', 'kavárna'],
                ],
            ],
            [
                'key' => 'front-gdpr',
                'page_name' => 'Ochrana osobních údajů',
                'route_name' => 'front.gdpr',
                'path' => '/ochrana-osobnich-udaju',
                'schema_type' => 'WebPage',
                'overrides' => [
                    'seo_title' => 'Ochrana osobních údajů | Gourmet Restaurant',
                    'seo_description' => 'Informace o zpracování a ochraně osobních údajů na webu Gourmet Restaurant v souladu s GDPR.',
                    'seo_keywords' => ['Gourmet Restaurant GDPR', 'ochrana osobních údajů', 'soukromí', 'GDPR'],
                    'business_name' => 'Gourmet Restaurant',
                    'aeo_summary' => 'Zásady ochrany osobních údajů společnosti Gourmet Group s.r.o. pro web Gourmet Restaurant.',
                    'aeo_search_intent' => 'Informační – ochrana osobních údajů a GDPR.',
                    'aeo_entities' => ['Gourmet Restaurant', 'GDPR', 'ochrana osobních údajů'],
                ],
            ],
            [
                'key' => 'front-cookies',
                'page_name' => 'Zásady cookies',
                'route_name' => 'front.cookies',
                'path' => '/zasady-cookies',
                'schema_type' => 'WebPage',
                'overrides' => [
                    'seo_title' => 'Zásady cookies | Gourmet Restaurant',
                    'seo_description' => 'Informace o používání cookies a správě souhlasů na webu Gourmet Restaurant.',
                    'seo_keywords' => ['Gourmet Restaurant cookies', 'zásady cookies', 'nastavení cookies', 'soukromí'],
                    'business_name' => 'Gourmet Restaurant',
                    'aeo_summary' => 'Zásady používání cookies a správy souhlasů na webu Gourmet Restaurant.',
                    'aeo_search_intent' => 'Informační – cookies, nastavení a odvolání souhlasu.',
                    'aeo_entities' => ['Gourmet Restaurant', 'cookies', 'nastavení cookies'],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function buildResolvedSeo(
        ?self $globalSeo,
        ?self $pageSeo,
        ?string $normalizedPath,
        ?string $currentUrl,
    ): array {
        $canonical = self::firstMeaningful(
            $pageSeo?->canonical_url,
            $globalSeo?->canonical_url,
            self::normalizeUrl($currentUrl),
            self::buildUrl($normalizedPath),
            self::buildUrl('/'),
        );

        $title = self::firstMeaningful(
            $pageSeo?->seo_title,
            $globalSeo?->seo_title,
            self::DEFAULT_TITLE,
        );

        $description = self::firstMeaningful(
            $pageSeo?->seo_description,
            $globalSeo?->seo_description,
            self::DEFAULT_DESCRIPTION,
        );

        $keywords = self::firstMeaningfulArray(
            self::normalizeStringList($pageSeo?->seo_keywords),
            self::normalizeStringList($globalSeo?->seo_keywords),
            [],
        );

        $socialProfiles = self::resolveSocialProfiles($pageSeo, $globalSeo);
        $sameAs = self::socialProfilesToSameAs($socialProfiles);

        $schemaType = self::firstMeaningful(
            $pageSeo?->schema_type,
            $globalSeo?->schema_type,
            self::defaultSchemaTypeForPath($normalizedPath),
        );

        $businessContext = self::resolveBusinessContext($pageSeo, $globalSeo);

        $structuredData = self::resolveStructuredData(
            schemaJson: self::firstMeaningfulArray(
                self::normalizeStructuredDataSource($pageSeo?->schema_json),
                self::normalizeStructuredDataSource($globalSeo?->schema_json),
                [],
            ),
            schemaType: $schemaType,
            canonical: $canonical,
            title: $title,
            description: $description,
            faqItems: self::firstMeaningfulArray(
                self::normalizeFaq($pageSeo?->aeo_faq),
                self::normalizeFaq($globalSeo?->aeo_faq),
                [],
            ),
            sameAs: $sameAs,
            businessContext: $businessContext,
        );

        return [
            'title' => $title,
            'description' => $description,
            'keywords' => $keywords,
            'canonical' => $canonical,
            'robots' => self::firstMeaningful($pageSeo?->robots, $globalSeo?->robots, self::DEFAULT_ROBOTS),
            'ogTitle' => self::firstMeaningful($pageSeo?->og_title, $globalSeo?->og_title, $title),
            'ogDescription' => self::firstMeaningful($pageSeo?->og_description, $globalSeo?->og_description, $description),
            'ogImage' => self::firstMeaningful($pageSeo?->og_image, $globalSeo?->og_image, self::defaultOgImage()),
            'ogType' => self::inferOgType($schemaType, $normalizedPath),
            'ogUrl' => self::firstMeaningful($canonical, self::normalizeUrl($currentUrl), self::buildUrl('/')),
            'ogLocale' => self::DEFAULT_OG_LOCALE,
            'ogSiteName' => self::DEFAULT_SITE_NAME,
            'twitterTitle' => self::firstMeaningful($pageSeo?->twitter_title, $globalSeo?->twitter_title, $title),
            'twitterDescription' => self::firstMeaningful($pageSeo?->twitter_description, $globalSeo?->twitter_description, $description),
            'twitterImage' => self::firstMeaningful($pageSeo?->twitter_image, $globalSeo?->twitter_image, self::defaultOgImage()),
            'twitterCard' => self::DEFAULT_TWITTER_CARD,
            'socialProfiles' => $socialProfiles,
            'structuredData' => $structuredData,
            'businessContext' => $businessContext,
            'aeoSummary' => self::firstMeaningful($pageSeo?->aeo_summary, $globalSeo?->aeo_summary),
            'aeoFaq' => self::firstMeaningfulArray(self::normalizeFaq($pageSeo?->aeo_faq), self::normalizeFaq($globalSeo?->aeo_faq), []),
            'aeoEntities' => self::firstMeaningfulArray(
                self::normalizeStringList($pageSeo?->aeo_entities),
                self::normalizeStringList($globalSeo?->aeo_entities),
                [],
            ),
            'aeoSearchIntent' => self::firstMeaningful($pageSeo?->aeo_search_intent, $globalSeo?->aeo_search_intent),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function fallbackSeo(?string $normalizedPath, ?string $currentUrl): array
    {
        return self::buildResolvedSeo(
            globalSeo: null,
            pageSeo: null,
            normalizedPath: $normalizedPath,
            currentUrl: $currentUrl,
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $schemaJson
     * @param  array<int, array{question: string, answer: string}>  $faqItems
     * @param  array<int, string>  $sameAs
     * @return array<string, mixed>|array<int, array<string, mixed>>
     */
    private static function resolveStructuredData(
        array $schemaJson,
        string $schemaType,
        string $canonical,
        string $title,
        string $description,
        array $faqItems,
        array $sameAs,
        array $businessContext,
    ): array {
        $structuredDataItems = $schemaJson;

        if ($structuredDataItems === []) {
            $structuredDataItems[] = self::buildBaseStructuredData(
                schemaType: $schemaType,
                canonical: $canonical,
                title: $title,
                description: $description,
                sameAs: $sameAs,
                businessContext: $businessContext,
            );
        } else {
            $structuredDataItems = self::injectSameAsIntoCustomStructuredData($structuredDataItems, $sameAs);
            $structuredDataItems = self::injectBusinessContextIntoCustomStructuredData($structuredDataItems, $businessContext);
        }

        if ($faqItems !== [] && ! self::containsFaqPage($structuredDataItems)) {
            $structuredDataItems[] = self::buildFaqSchema($faqItems);
        }

        return count($structuredDataItems) === 1
            ? $structuredDataItems[0]
            : $structuredDataItems;
    }

    /**
     * @param  array<int, array<string, mixed>>  $structuredDataItems
     */
    private static function containsFaqPage(array $structuredDataItems): bool
    {
        foreach ($structuredDataItems as $item) {
            $type = $item['@type'] ?? null;

            if ($type === 'FAQPage') {
                return true;
            }

            if (is_array($type) && in_array('FAQPage', $type, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<int, array{question: string, answer: string}>  $faqItems
     * @return array<string, mixed>
     */
    private static function buildFaqSchema(array $faqItems): array
    {
        return [
            '@context' => self::DEFAULT_SCHEMA_CONTEXT,
            '@type' => 'FAQPage',
            'mainEntity' => array_map(
                static fn (array $faq): array => [
                    '@type' => 'Question',
                    'name' => $faq['question'],
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $faq['answer'],
                    ],
                ],
                $faqItems,
            ),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function buildBaseStructuredData(
        string $schemaType,
        string $canonical,
        string $title,
        string $description,
        array $sameAs,
        array $businessContext,
    ): array {
        $schema = [
            '@context' => self::DEFAULT_SCHEMA_CONTEXT,
            '@type' => $schemaType,
            'name' => $title,
            'description' => $description,
            'url' => $canonical,
            'inLanguage' => 'cs-CZ',
        ];

        if ($sameAs !== []) {
            $schema['sameAs'] = $sameAs;
        }

        $businessName = self::firstMeaningful($businessContext['name'] ?? null);

        if ($businessName !== null && self::shouldAttachBusinessContextToType($schemaType)) {
            $schema['name'] = $businessName;
        }

        return self::mergeBusinessContextIntoSchemaItem($schema, $businessContext);
    }

    /**
     * @return array<string, mixed>
     */
    private static function resolveBusinessContext(?self $pageSeo, ?self $globalSeo): array
    {
        $areaServed = self::firstMeaningfulArray(
            self::normalizeStringList($pageSeo?->area_served),
            self::normalizeStringList($globalSeo?->area_served),
            [],
        );

        $availableLanguages = self::firstMeaningfulArray(
            self::normalizeStringList($pageSeo?->available_languages),
            self::normalizeStringList($globalSeo?->available_languages),
            [],
        );

        $address = self::buildPostalAddress(
            streetAddress: self::firstMeaningful($pageSeo?->street_address, $globalSeo?->street_address),
            addressLocality: self::firstMeaningful($pageSeo?->address_locality, $globalSeo?->address_locality),
            postalCode: self::firstMeaningful($pageSeo?->postal_code, $globalSeo?->postal_code),
            addressCountry: self::firstMeaningful($pageSeo?->address_country, $globalSeo?->address_country),
        );

        $geo = self::buildGeoCoordinates(
            latitude: self::firstMeaningful(
                $pageSeo?->latitude !== null ? (string) $pageSeo->latitude : null,
                $globalSeo?->latitude !== null ? (string) $globalSeo->latitude : null,
            ),
            longitude: self::firstMeaningful(
                $pageSeo?->longitude !== null ? (string) $pageSeo->longitude : null,
                $globalSeo?->longitude !== null ? (string) $globalSeo->longitude : null,
            ),
        );

        return [
            'name' => self::firstMeaningful($pageSeo?->business_name, $globalSeo?->business_name),
            'address' => $address,
            'geo' => $geo,
            'areaServed' => $areaServed,
            'availableLanguage' => $availableLanguages,
            'offersOnline' => (bool) ($pageSeo?->offers_online ?? $globalSeo?->offers_online ?? false),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function buildPostalAddress(
        ?string $streetAddress,
        ?string $addressLocality,
        ?string $postalCode,
        ?string $addressCountry,
    ): ?array {
        $address = array_filter([
            'streetAddress' => $streetAddress,
            'addressLocality' => $addressLocality,
            'postalCode' => $postalCode,
            'addressCountry' => $addressCountry,
        ], static fn (mixed $value): bool => is_string($value) && trim($value) !== '');

        return $address === [] ? null : $address;
    }

    /**
     * @return array<string, string>|null
     */
    private static function buildGeoCoordinates(?string $latitude, ?string $longitude): ?array
    {
        if ($latitude === null || $longitude === null) {
            return null;
        }

        if (! is_numeric($latitude) || ! is_numeric($longitude)) {
            return null;
        }

        return [
            'latitude' => $latitude,
            'longitude' => $longitude,
        ];
    }

    /**
     * @return array{facebook: ?string, instagram: ?string, linkedin: ?string, youtube: ?string}
     */
    private static function resolveSocialProfiles(?self $pageSeo, ?self $globalSeo): array
    {
        return [
            'facebook' => self::firstMeaningful($pageSeo?->social_facebook_url, $globalSeo?->social_facebook_url),
            'instagram' => self::firstMeaningful($pageSeo?->social_instagram_url, $globalSeo?->social_instagram_url),
            'linkedin' => self::firstMeaningful($pageSeo?->social_linkedin_url, $globalSeo?->social_linkedin_url),
            'youtube' => self::firstMeaningful($pageSeo?->social_youtube_url, $globalSeo?->social_youtube_url),
        ];
    }

    /**
     * @param  array{facebook: ?string, instagram: ?string, linkedin: ?string, youtube: ?string}  $socialProfiles
     * @return array<int, string>
     */
    private static function socialProfilesToSameAs(array $socialProfiles): array
    {
        return self::normalizeUrlList($socialProfiles);
    }

    /**
     * @param  array<int, array<string, mixed>>  $structuredDataItems
     * @param  array<int, string>  $sameAs
     * @return array<int, array<string, mixed>>
     */
    private static function injectSameAsIntoCustomStructuredData(array $structuredDataItems, array $sameAs): array
    {
        if ($sameAs === []) {
            return $structuredDataItems;
        }

        foreach ($structuredDataItems as $itemIndex => $item) {
            [$updatedItem, $applied] = self::attachSameAsToStructuredDataItem($item, $sameAs);

            if (! $applied) {
                continue;
            }

            $structuredDataItems[$itemIndex] = $updatedItem;

            return $structuredDataItems;
        }

        return $structuredDataItems;
    }

    /**
     * @param  array<string, mixed>  $item
     * @param  array<int, string>  $sameAs
     * @return array{0: array<string, mixed>, 1: bool}
     */
    private static function attachSameAsToStructuredDataItem(array $item, array $sameAs): array
    {
        if (array_key_exists('sameAs', $item)) {
            $item['sameAs'] = self::mergeSameAs($item['sameAs'], $sameAs);

            return [$item, true];
        }

        if (self::canAttachSameAsToType($item['@type'] ?? null)) {
            $item['sameAs'] = $sameAs;

            return [$item, true];
        }

        if (! isset($item['@graph']) || ! is_array($item['@graph'])) {
            return [$item, false];
        }

        foreach ($item['@graph'] as $graphItemIndex => $graphItem) {
            if (! is_array($graphItem)) {
                continue;
            }

            if (
                ! array_key_exists('sameAs', $graphItem)
                && ! self::canAttachSameAsToType($graphItem['@type'] ?? null)
            ) {
                continue;
            }

            $graphItem['sameAs'] = self::mergeSameAs($graphItem['sameAs'] ?? [], $sameAs);
            $item['@graph'][$graphItemIndex] = $graphItem;

            return [$item, true];
        }

        return [$item, false];
    }

    /**
     * @param  array<int, string>  $sameAs
     * @return array<int, string>
     */
    private static function mergeSameAs(mixed $existingSameAs, array $sameAs): array
    {
        return self::normalizeUrlList([
            ...self::normalizeUrlList($existingSameAs),
            ...$sameAs,
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $structuredDataItems
     * @param  array<string, mixed>  $businessContext
     * @return array<int, array<string, mixed>>
     */
    private static function injectBusinessContextIntoCustomStructuredData(array $structuredDataItems, array $businessContext): array
    {
        if (! self::hasUsableBusinessContext($businessContext)) {
            return $structuredDataItems;
        }

        foreach ($structuredDataItems as $itemIndex => $item) {
            [$updatedItem, $applied] = self::attachBusinessContextToStructuredDataItem($item, $businessContext);

            if (! $applied) {
                continue;
            }

            $structuredDataItems[$itemIndex] = $updatedItem;

            return $structuredDataItems;
        }

        return $structuredDataItems;
    }

    /**
     * @param  array<string, mixed>  $item
     * @param  array<string, mixed>  $businessContext
     * @return array{0: array<string, mixed>, 1: bool}
     */
    private static function attachBusinessContextToStructuredDataItem(array $item, array $businessContext): array
    {
        $updatedItem = self::mergeBusinessContextIntoSchemaItem($item, $businessContext);

        if ($updatedItem !== $item) {
            return [$updatedItem, true];
        }

        if (! isset($item['@graph']) || ! is_array($item['@graph'])) {
            return [$item, false];
        }

        foreach ($item['@graph'] as $graphItemIndex => $graphItem) {
            if (! is_array($graphItem)) {
                continue;
            }

            $updatedGraphItem = self::mergeBusinessContextIntoSchemaItem($graphItem, $businessContext);

            if ($updatedGraphItem === $graphItem) {
                continue;
            }

            $item['@graph'][$graphItemIndex] = $updatedGraphItem;

            return [$item, true];
        }

        return [$item, false];
    }

    /**
     * @param  array<string, mixed>  $schemaItem
     * @param  array<string, mixed>  $businessContext
     * @return array<string, mixed>
     */
    private static function mergeBusinessContextIntoSchemaItem(array $schemaItem, array $businessContext): array
    {
        if (! self::shouldAttachBusinessContextToType($schemaItem['@type'] ?? null)) {
            return $schemaItem;
        }

        $businessName = self::firstMeaningful($businessContext['name'] ?? null);

        if ($businessName !== null && self::firstMeaningful($schemaItem['name'] ?? null) === null) {
            $schemaItem['name'] = $businessName;
        }

        $postalAddress = self::normalizePostalAddressForSchema($businessContext['address'] ?? null);

        if ($postalAddress !== null) {
            if (! isset($schemaItem['address'])) {
                $schemaItem['address'] = $postalAddress;
            } elseif (is_array($schemaItem['address'])) {
                $schemaItem['address'] = array_merge($postalAddress, $schemaItem['address']);
                $schemaItem['address']['@type'] = 'PostalAddress';
            }
        }

        $geoCoordinates = self::normalizeGeoCoordinatesForSchema($businessContext['geo'] ?? null);

        if ($geoCoordinates !== null) {
            if (! isset($schemaItem['geo'])) {
                $schemaItem['geo'] = $geoCoordinates;
            } elseif (is_array($schemaItem['geo'])) {
                $schemaItem['geo'] = array_merge($geoCoordinates, $schemaItem['geo']);
                $schemaItem['geo']['@type'] = 'GeoCoordinates';
            }
        }

        $areaServed = self::buildAreaServedForSchema($businessContext);

        if ($areaServed !== []) {
            $schemaItem['areaServed'] = self::mergeStringValues($schemaItem['areaServed'] ?? [], $areaServed);
        }

        $availableLanguage = self::normalizeStringList($businessContext['availableLanguage'] ?? []);

        if ($availableLanguage !== []) {
            $schemaItem['availableLanguage'] = self::mergeStringValues(
                $schemaItem['availableLanguage'] ?? [],
                $availableLanguage,
            );
        }

        return $schemaItem;
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function normalizePostalAddressForSchema(mixed $address): ?array
    {
        if (! is_array($address)) {
            return null;
        }

        $normalizedAddress = self::buildPostalAddress(
            streetAddress: self::firstMeaningful($address['streetAddress'] ?? null),
            addressLocality: self::firstMeaningful($address['addressLocality'] ?? null),
            postalCode: self::firstMeaningful($address['postalCode'] ?? null),
            addressCountry: self::firstMeaningful($address['addressCountry'] ?? null),
        );

        if ($normalizedAddress === null) {
            return null;
        }

        return ['@type' => 'PostalAddress', ...$normalizedAddress];
    }

    /**
     * @return array<string, string>|null
     */
    private static function normalizeGeoCoordinatesForSchema(mixed $geo): ?array
    {
        if (! is_array($geo)) {
            return null;
        }

        $normalizedGeo = self::buildGeoCoordinates(
            latitude: self::firstMeaningful($geo['latitude'] ?? null),
            longitude: self::firstMeaningful($geo['longitude'] ?? null),
        );

        if ($normalizedGeo === null) {
            return null;
        }

        return ['@type' => 'GeoCoordinates', ...$normalizedGeo];
    }

    /**
     * @param  array<string, mixed>  $businessContext
     * @return array<int, string>
     */
    private static function buildAreaServedForSchema(array $businessContext): array
    {
        $areaServed = self::normalizeStringList($businessContext['areaServed'] ?? []);

        if (($businessContext['offersOnline'] ?? false) === true) {
            $areaServed[] = 'Online';
        }

        return array_values(array_unique($areaServed));
    }

    /**
     * @param  array<string, mixed>  $businessContext
     */
    private static function hasUsableBusinessContext(array $businessContext): bool
    {
        return self::firstMeaningful($businessContext['name'] ?? null) !== null
            || self::normalizePostalAddressForSchema($businessContext['address'] ?? null) !== null
            || self::normalizeGeoCoordinatesForSchema($businessContext['geo'] ?? null) !== null
            || self::normalizeStringList($businessContext['areaServed'] ?? []) !== []
            || self::normalizeStringList($businessContext['availableLanguage'] ?? []) !== []
            || (($businessContext['offersOnline'] ?? false) === true);
    }

    private static function shouldAttachBusinessContextToType(mixed $type): bool
    {
        $supportedTypes = [
            'localbusiness',
            'professionalservice',
            'restaurant',
            'foodestablishment',
            'person',
            'website',
            'webpage',
            'service',
        ];

        if (is_string($type)) {
            return in_array(strtolower(trim($type)), $supportedTypes, true);
        }

        if (! is_array($type)) {
            return false;
        }

        foreach ($type as $item) {
            if (! is_string($item)) {
                continue;
            }

            if (in_array(strtolower(trim($item)), $supportedTypes, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<int, string>  $incomingValues
     * @return array<int, string>
     */
    private static function mergeStringValues(mixed $existingValues, array $incomingValues): array
    {
        return array_values(array_unique([
            ...self::normalizeStringList($existingValues),
            ...self::normalizeStringList($incomingValues),
        ]));
    }

    private static function canAttachSameAsToType(mixed $type): bool
    {
        if (is_string($type)) {
            return strtolower(trim($type)) !== 'faqpage';
        }

        if (! is_array($type)) {
            return false;
        }

        $normalizedTypes = array_map(
            static fn (mixed $item): string => is_string($item) ? strtolower(trim($item)) : '',
            $type,
        );

        $normalizedTypes = array_values(array_filter($normalizedTypes, static fn (string $item): bool => $item !== ''));

        return $normalizedTypes !== [] && ! in_array('faqpage', $normalizedTypes, true);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function normalizeStructuredDataSource(mixed $value): array
    {
        if (! is_array($value) || $value === []) {
            return [];
        }

        if (self::isAssoc($value)) {
            return [$value];
        }

        return array_values(array_filter($value, static fn (mixed $item): bool => is_array($item)));
    }

    /**
     * @return array<int, array{question: string, answer: string}>
     */
    private static function normalizeFaq(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $faqItems = [];

        foreach ($value as $item) {
            if (! is_array($item)) {
                continue;
            }

            $question = self::firstMeaningful($item['question'] ?? null);
            $answer = self::firstMeaningful($item['answer'] ?? null);

            if ($question === null || $answer === null) {
                continue;
            }

            $faqItems[] = [
                'question' => $question,
                'answer' => $answer,
            ];
        }

        return $faqItems;
    }

    /**
     * @return array<int, string>
     */
    private static function normalizeStringList(mixed $value): array
    {
        if (is_string($value)) {
            $parts = array_map(
                static fn (string $item): string => trim($item),
                explode(',', $value),
            );

            return array_values(array_filter($parts, static fn (string $item): bool => $item !== ''));
        }

        if (! is_array($value)) {
            return [];
        }

        $items = [];

        foreach ($value as $item) {
            if (is_string($item)) {
                $normalized = trim($item);

                if ($normalized !== '') {
                    $items[] = $normalized;
                }

                continue;
            }

            if (is_array($item)) {
                $nestedValue = self::firstMeaningful($item['value'] ?? null);

                if ($nestedValue !== null) {
                    $items[] = $nestedValue;
                }
            }
        }

        return $items;
    }

    private static function inferOgType(string $schemaType, ?string $path): string
    {
        $normalizedSchemaType = strtolower($schemaType);

        if ($normalizedSchemaType === 'website') {
            return 'website';
        }

        if ($normalizedSchemaType === 'article') {
            return 'article';
        }

        return $path === '/' ? 'website' : 'webpage';
    }

    private static function defaultSchemaTypeForPath(?string $path): string
    {
        return $path === '/' ? 'WebSite' : 'WebPage';
    }

    private static function defaultOgImage(): string
    {
        return self::buildUrl(self::DEFAULT_OG_IMAGE_PATH);
    }

    private static function buildUrl(?string $path): string
    {
        $baseUrl = rtrim((string) config('app.url', ''), '/');

        if ($baseUrl === '') {
            $baseUrl = rtrim(url('/'), '/');
        }

        if ($path === null || $path === '') {
            return $baseUrl;
        }

        return $baseUrl.'/'.ltrim($path, '/');
    }

    private static function normalizeUrl(?string $url): ?string
    {
        if ($url === null) {
            return null;
        }

        $normalized = trim($url);

        return $normalized === '' ? null : $normalized;
    }

    private static function normalizePath(?string $path): ?string
    {
        if ($path === null) {
            return null;
        }

        $normalized = '/'.trim($path, '/');

        return $normalized === '/' ? '/' : rtrim($normalized, '/');
    }

    /**
     * @return array<int, string>
     */
    private static function normalizeUrlList(mixed $value): array
    {
        if (is_string($value)) {
            $url = self::firstMeaningful($value);

            return $url === null ? [] : [$url];
        }

        if (! is_array($value)) {
            return [];
        }

        $urls = [];

        foreach ($value as $item) {
            if (! is_string($item)) {
                continue;
            }

            $url = self::firstMeaningful($item);

            if ($url !== null) {
                $urls[] = $url;
            }
        }

        return array_values(array_unique($urls));
    }

    /**
     * @param  array<int, mixed>  $values
     */
    private static function firstMeaningful(mixed ...$values): ?string
    {
        foreach ($values as $value) {
            if (! is_string($value)) {
                continue;
            }

            $normalized = trim($value);

            if ($normalized !== '') {
                return $normalized;
            }
        }

        return null;
    }

    /**
     * @param  array<int, array<int|string, mixed>>  $values
     * @return array<int, mixed>
     */
    private static function firstMeaningfulArray(array ...$values): array
    {
        foreach ($values as $value) {
            if ($value !== []) {
                return $value;
            }
        }

        return [];
    }

    /**
     * @param  array<mixed>  $array
     */
    private static function isAssoc(array $array): bool
    {
        return ! array_is_list($array);
    }

    /**
     * @return array<string, mixed>
     */
    private static function globalRecord(): array
    {
        return array_merge(self::baseRecord(), [
            'key' => self::KEY_GLOBAL,
            'page_name' => 'Globální SEO',
            'is_global' => true,
            'schema_type' => 'WebSite',
            'business_name' => 'U Sejmona pod hájkem',
            'seo_title' => 'U Sejmona pod hájkem | Restaurace a občerstvení',
            'seo_description' => 'Restaurace U Sejmona pod hájkem nabízí příjemné posezení. Aktuální nabídku jídel a nápojů najdete v našem jídelním a nápojovém lístku.',
            'seo_keywords' => ['restaurace', 'U Sejmona pod hájkem', 'jídlo', 'pití', 'občerstvení'],
            'aeo_summary' => 'Restaurace U Sejmona pod hájkem – aktuální nabídku najdete v jídelním a nápojovém lístku.',
            'aeo_search_intent' => 'Informační – hledání restaurace a jejích služeb.',
            'aeo_entities' => ['Restaurace U Sejmona pod hájkem', 'restaurace'],
            'notes' => 'Výchozí fallback pro všechny stránky bez vlastní SEO konfigurace.',
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private static function frontPageRecord(
        string $key,
        string $pageName,
        string $routeName,
        string $path,
        string $schemaType,
        array $overrides = [],
    ): array {
        return array_merge(self::baseRecord(), [
            'key' => $key,
            'page_name' => $pageName,
            'route_name' => $routeName,
            'path' => $path,
            'schema_type' => $schemaType,
        ], $overrides);
    }

    /**
     * @return array<string, mixed>
     */
    private static function baseRecord(): array
    {
        return [
            'key' => null,
            'page_name' => null,
            'route_name' => null,
            'path' => null,
            'is_global' => false,
            'is_active' => true,
            'seo_title' => null,
            'seo_description' => null,
            'seo_keywords' => null,
            'canonical_url' => null,
            'robots' => 'index, follow',
            'og_title' => null,
            'og_description' => null,
            'og_image' => null,
            'twitter_title' => null,
            'twitter_description' => null,
            'twitter_image' => null,
            'social_facebook_url' => null,
            'social_instagram_url' => null,
            'social_linkedin_url' => null,
            'social_youtube_url' => null,
            'business_name' => null,
            'street_address' => null,
            'address_locality' => null,
            'postal_code' => null,
            'address_country' => null,
            'area_served' => null,
            'available_languages' => null,
            'latitude' => null,
            'longitude' => null,
            'offers_online' => false,
            'schema_type' => null,
            'schema_json' => null,
            'aeo_summary' => null,
            'aeo_faq' => null,
            'aeo_entities' => null,
            'aeo_search_intent' => null,
            'notes' => null,
        ];
    }
}
