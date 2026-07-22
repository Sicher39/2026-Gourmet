<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\SeoPage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SeoPageDefaultsTest extends TestCase
{
    /**
     * Expected public indexable GET page keys from frontPageDefinitions
     * and their corresponding route names + paths.
     *
     * @return array<string, array{route_name: string, path: string}>
     */
    public static function publicPageKeysProvider(): array
    {
        return [
            'home' => ['route_name' => 'front.index', 'path' => '/'],
            'drink menu' => ['route_name' => 'front.drinkMenu', 'path' => '/napojovy-listek'],
            'food menu' => ['route_name' => 'front.foodMenu', 'path' => '/jidelni-listek'],
            'contact' => ['route_name' => 'front.contact', 'path' => '/kontakt'],
            'reservation terms' => ['route_name' => 'front.reservationTerms', 'path' => '/podminky-rezervace'],
            'gdpr' => ['route_name' => 'front.gdpr', 'path' => '/ochrana-osobnich-udaju'],
            'galleries' => ['route_name' => 'front.galleries', 'path' => '/galerie'],
            'cookies' => ['route_name' => 'front.cookies', 'path' => '/zasady-cookies'],
            'reservation' => ['route_name' => 'restaurant.reservation', 'path' => '/reservation'],
        ];
    }

    #[DataProvider('publicPageKeysProvider')]
    public function test_public_page_definition_has_expected_route_and_path(string $route_name, string $path): void
    {
        $definitions = SeoPage::frontPageDefinitions();

        $matching = collect($definitions)
            ->filter(fn (array $def): bool => ($def['route_name'] ?? '') === $route_name)
            ->first();

        $this->assertNotNull($matching, "No frontPageDefinition found for route '{$route_name}'.");
        $this->assertSame($path, $matching['path'] ?? null, "Path mismatch for route '{$route_name}'.");
    }

    public function test_global_record_has_correct_key(): void
    {
        $records = SeoPage::defaultRecords();
        $global = collect($records)->firstWhere('key', SeoPage::KEY_GLOBAL);

        $this->assertNotNull($global, 'Global record must be present in defaultRecords.');
        $this->assertTrue((bool) ($global['is_global'] ?? false), 'Global record must have is_global = true.');
        $this->assertSame('WebSite', $global['schema_type'] ?? null);
        $this->assertSame('U Sejmona pod hájkem', $global['business_name'] ?? null);
    }

    public function test_all_default_records_have_required_fields(): void
    {
        $requiredFields = ['key', 'page_name', 'is_global', 'is_active', 'route_name', 'path', 'schema_type'];

        foreach (SeoPage::defaultRecords() as $record) {
            foreach ($requiredFields as $field) {
                $this->assertArrayHasKey($field, $record, "Record '{$record['key']}' is missing field '{$field}'.");
            }
        }
    }

    /**
     * Ensure no foreign-project therapy/crime-prevention terms leak into defaults.
     */
    public function test_default_records_do_not_contain_copied_therapy_terms(): void
    {
        $forbiddenKeys = [
            'front-therapy',
            'front-marriage-counseling',
            'front-individual-consultations',
            'front-coaching',
            'front-media',
        ];

        $keys = collect(SeoPage::defaultRecords())->pluck('key')->all();

        foreach ($forbiddenKeys as $forbiddenKey) {
            $this->assertNotContains($forbiddenKey, $keys, "Forbidden key '{$forbiddenKey}' found in default records.");
        }
    }

    /**
     * Ensure no foreign-project terms appear in any textual SEO content.
     */
    public function test_default_records_do_not_contain_copied_therapy_content(): void
    {
        $forbiddenPhrases = [
            'terapie',
            'prevence kriminality',
            'párová',
            'policejní',
            'vyšetřovatel',
            'individuální konzultace',
            'koučink',
            'kriminalita',
        ];

        foreach (SeoPage::defaultRecords() as $record) {
            $serialized = json_encode($record, JSON_THROW_ON_ERROR);

            foreach ($forbiddenPhrases as $phrase) {
                $this->assertStringNotContainsString(
                    $phrase,
                    $serialized,
                    "Forbidden phrase '{$phrase}' found in record '{$record['key']}'.",
                );
            }
        }
    }

    /**
     * All non-global page definitions must have an active state and a route.
     */
    public function test_front_page_definitions_are_active_with_routes(): void
    {
        $definitions = SeoPage::frontPageDefinitions();

        $this->assertNotEmpty($definitions);

        foreach ($definitions as $def) {
            $this->assertNotEmpty($def['route_name'] ?? '', "Key '{$def['key']}' must have a route_name.");
            $this->assertNotEmpty($def['path'] ?? '', "Key '{$def['key']}' must have a path.");
            $this->assertFalse((bool) ($def['is_global'] ?? false), "Front page '{$def['key']}' must not be is_global.");
        }
    }

    public function test_default_canonical_url_is_null_or_empty(): void
    {
        foreach (SeoPage::defaultRecords() as $record) {
            $canonical = $record['canonical_url'] ?? null;

            if ($canonical !== null) {
                $canonical = trim((string) $canonical);
            }

            $this->assertTrue(
                $canonical === null || $canonical === '',
                "Record '{$record['key']}' has a hardcoded canonical_url; expected null/empty so request URL is used.",
            );
        }
    }

    public function test_default_og_image_points_to_existing_logo_path(): void
    {
        $records = SeoPage::defaultRecords();
        $global = collect($records)->firstWhere('key', SeoPage::KEY_GLOBAL);
        $ogImage = $global['og_image'] ?? null;

        // og_image is null in defaults (falls back to DEFAULT_OG_IMAGE_PATH via resolve)
        // Verify the constant path points to a real file.
        $ref = new \ReflectionClass(SeoPage::class);
        $path = $ref->getConstant('DEFAULT_OG_IMAGE_PATH');

        $this->assertIsString($path, 'DEFAULT_OG_IMAGE_PATH constant must be a string.');
        $this->assertNotEmpty($path);

        $fullPath = public_path(ltrim((string) $path, '/'));

        $this->assertFileExists($fullPath, "Default OG image does not exist at: {$fullPath}");
    }

    public function test_reservation_path_matches_actual_route(): void
    {
        $definitions = SeoPage::frontPageDefinitions();
        $reservation = collect($definitions)->firstWhere('key', 'reservation');

        $this->assertNotNull($reservation, 'Reservation definition must exist.');
        $this->assertSame('/reservation', $reservation['path'] ?? null, 'Reservation path must be /reservation.');
        $this->assertSame('restaurant.reservation', $reservation['route_name'] ?? null);
    }

    public function test_default_records_count_matches_expected_pages(): void
    {
        $records = SeoPage::defaultRecords();

        // global + 9 public pages = 10
        $this->assertCount(10, $records, 'Expected exactly 10 default records (global + 9 public pages).');
    }

    public function test_no_default_record_has_load_more_or_json_paths(): void
    {
        $excludedPaths = ['/galerie/nacist-dalsi'];

        foreach (SeoPage::defaultRecords() as $record) {
            $path = $record['path'] ?? '';
            $this->assertNotContains($path, $excludedPaths, "Record '{$record['key']}' has excluded load-more/JSON path.");
        }

        $postKeys = collect(SeoPage::defaultRecords())->pluck('key')->all();
        $this->assertNotContains('front.galleries.load-more', $postKeys);
    }
}
