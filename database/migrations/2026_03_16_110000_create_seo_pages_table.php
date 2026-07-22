<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_pages', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->string('page_name');
            $table->string('route_name')->nullable()->index();
            $table->string('path')->nullable()->index();
            $table->boolean('is_global')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->json('seo_keywords')->nullable();
            $table->string('canonical_url', 2048)->nullable();
            $table->string('robots')->nullable();
            $table->string('og_title')->nullable();
            $table->text('og_description')->nullable();
            $table->string('og_image', 2048)->nullable();
            $table->string('twitter_title')->nullable();
            $table->text('twitter_description')->nullable();
            $table->string('twitter_image', 2048)->nullable();
            $table->string('schema_type')->nullable();
            $table->json('schema_json')->nullable();
            $table->text('aeo_summary')->nullable();
            $table->json('aeo_faq')->nullable();
            $table->json('aeo_entities')->nullable();
            $table->text('aeo_search_intent')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        DB::table('seo_pages')->insert($this->defaultRecords());
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_pages');
    }

    /**
     * Seed records at migration time only contain columns available in this
     * migration. Later migrations add social, geo, and business columns.
     *
     * @return array<int, array<string, mixed>>
     */
    private function defaultRecords(): array
    {
        $baseRecord = [
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
            'schema_type' => null,
            'schema_json' => null,
            'aeo_summary' => null,
            'aeo_faq' => null,
            'aeo_entities' => null,
            'aeo_search_intent' => null,
            'notes' => null,
        ];

        $businessName = 'U Sejmona pod hájkem';

        return [
            array_merge($baseRecord, [
                'key' => 'global',
                'page_name' => 'Globální SEO',
                'is_global' => true,
                'schema_type' => 'WebSite',
                'seo_title' => 'U Sejmona pod hájkem | Restaurace a občerstvení',
                'seo_description' => 'Restaurace U Sejmona pod hájkem nabízí příjemné posezení. Aktuální nabídku jídel a nápojů najdete v našem jídelním a nápojovém lístku.',
                'seo_keywords' => json_encode(['restaurace', 'U Sejmona pod hájkem', 'jídlo', 'pití', 'občerstvení'], JSON_THROW_ON_ERROR),
                'aeo_summary' => 'Restaurace U Sejmona pod hájkem – aktuální nabídku najdete v jídelním a nápojovém lístku.',
                'aeo_search_intent' => 'Informační – hledání restaurace a jejích služeb.',
                'aeo_entities' => json_encode(['Restaurace U Sejmona pod hájkem', 'restaurace'], JSON_THROW_ON_ERROR),
                'notes' => 'Výchozí fallback pro všechny stránky bez vlastní SEO konfigurace.',
            ]),
            array_merge($baseRecord, [
                'key' => 'front-index',
                'page_name' => 'Domů',
                'route_name' => 'front.index',
                'path' => '/',
                'schema_type' => 'WebPage',
                'seo_title' => 'U Sejmona pod hájkem | Restaurace',
                'seo_description' => 'Restaurace U Sejmona pod hájkem – aktuální nabídku najdete v jídelním a nápojovém lístku.',
                'seo_keywords' => json_encode(['restaurace', 'U Sejmona pod hájkem', 'jídlo', 'pití', 'posezení'], JSON_THROW_ON_ERROR),
                'aeo_summary' => 'Restaurace U Sejmona pod hájkem – aktuální nabídku najdete v jídelním a nápojovém lístku.',
                'aeo_search_intent' => 'Informační – hledání restaurace, jídelního lístku a kontaktů.',
                'aeo_entities' => json_encode(['Restaurace U Sejmona pod hájkem', 'restaurace', 'jídelní lístek', 'nápojový lístek'], JSON_THROW_ON_ERROR),
                'aeo_faq' => json_encode([
                    ['question' => 'Kde se restaurace nachází?', 'answer' => 'Přesnou adresu a mapu naleznete na stránce Kontakt.'],
                    ['question' => 'Jaká je otevírací doba?', 'answer' => 'Aktuální otevírací dobu naleznete na našem webu nebo nás kontaktujte telefonicky.'],
                ], JSON_THROW_ON_ERROR),
            ]),
            array_merge($baseRecord, [
                'key' => 'front-drink-menu',
                'page_name' => 'Nápojový lístek',
                'route_name' => 'front.drinkMenu',
                'path' => '/napojovy-listek',
                'schema_type' => 'WebPage',
                'seo_title' => 'Nápojový lístek | U Sejmona pod hájkem',
                'seo_description' => 'Prohlédněte si aktuální nabídku nápojů – od točeného piva přes vína až po nealkoholické nápoje.',
                'seo_keywords' => json_encode(['nápojový lístek', 'pivo', 'víno', 'nealko', 'U Sejmona pod hájkem'], JSON_THROW_ON_ERROR),
                'aeo_summary' => 'Nápojový lístek restaurace U Sejmona pod hájkem – aktuální nabídku najdete v lístku.',
                'aeo_search_intent' => 'Informační – hledání nabídky nápojů v restauraci.',
                'aeo_entities' => json_encode(['Nápojový lístek', 'Restaurace U Sejmona pod hájkem', 'pivo', 'víno', 'nápoje'], JSON_THROW_ON_ERROR),
                'aeo_faq' => json_encode([
                    ['question' => 'Jaké pivo čepujete?', 'answer' => 'Aktuální nabídku čepovaných piv naleznete na našem nápojovém lístku.'],
                    ['question' => 'Máte i nealkoholické nápoje?', 'answer' => 'Aktuální nabídku najdete v nápojovém lístku.'],
                ], JSON_THROW_ON_ERROR),
            ]),
            array_merge($baseRecord, [
                'key' => 'front-food-menu',
                'page_name' => 'Jídelní lístek',
                'route_name' => 'front.foodMenu',
                'path' => '/jidelni-listek',
                'schema_type' => 'WebPage',
                'seo_title' => 'Jídelní lístek | U Sejmona pod hájkem',
                'seo_description' => 'Objevte naši nabídku jídel. Aktuální jídelní lístek najdete na našem webu.',
                'seo_keywords' => json_encode(['jídelní lístek', 'restaurace', 'česká kuchyně', 'U Sejmona pod hájkem'], JSON_THROW_ON_ERROR),
                'aeo_summary' => 'Jídelní lístek restaurace U Sejmona pod hájkem – aktuální nabídku najdete v lístku.',
                'aeo_search_intent' => 'Informační – hledání jídelního lístku a nabídky jídel v restauraci.',
                'aeo_entities' => json_encode(['Jídelní lístek', 'Restaurace U Sejmona pod hájkem', 'česká kuchyně', 'jídlo'], JSON_THROW_ON_ERROR),
                'aeo_faq' => json_encode([
                    ['question' => 'Nabízíte i bezmasá jídla?', 'answer' => 'Aktuální nabídku najdete v jídelním lístku.'],
                ], JSON_THROW_ON_ERROR),
            ]),
            array_merge($baseRecord, [
                'key' => 'front-contact',
                'page_name' => 'Kontakt',
                'route_name' => 'front.contact',
                'path' => '/kontakt',
                'schema_type' => 'ContactPage',
                'seo_title' => 'Kontakt | U Sejmona pod hájkem',
                'seo_description' => 'Kontaktujte nás – telefonicky, e-mailem nebo osobně. Těšíme se na vaši návštěvu v restauraci U Sejmona pod hájkem.',
                'seo_keywords' => json_encode(['kontakt', 'restaurace', 'telefon', 'adresa', 'U Sejmona pod hájkem'], JSON_THROW_ON_ERROR),
                'aeo_summary' => 'Kontaktní informace restaurace U Sejmona pod hájkem – adresa, telefon, e-mail.',
                'aeo_search_intent' => 'Informační – hledání kontaktních údajů a adresy restaurace.',
                'aeo_entities' => json_encode(['Kontakt', 'Restaurace U Sejmona pod hájkem', 'adresa', 'telefon'], JSON_THROW_ON_ERROR),
            ]),
            array_merge($baseRecord, [
                'key' => 'front-reservation-terms',
                'page_name' => 'Podmínky rezervací',
                'route_name' => 'front.reservationTerms',
                'path' => '/podminky-rezervace',
                'schema_type' => 'WebPage',
                'seo_title' => 'Podmínky rezervací | U Sejmona pod hájkem',
                'seo_description' => 'Seznamte se s podmínkami rezervace stolu v restauraci U Sejmona pod hájkem, včetně pravidel změn a zrušení rezervace.',
                'seo_keywords' => json_encode(['podmínky rezervací', 'rezervace stolu', 'storno rezervace', 'restaurace', 'U Sejmona pod hájkem'], JSON_THROW_ON_ERROR),
                'aeo_summary' => 'Podmínky rezervací určují pravidla rezervace stolu, změn termínu, storna a související komunikace se zákazníkem restaurace U Sejmona pod hájkem.',
                'aeo_search_intent' => 'Informační – hledání pravidel pro rezervaci stolu v restauraci.',
                'aeo_entities' => json_encode(['Podmínky rezervací', 'Rezervace stolu', 'Restaurace U Sejmona pod hájkem', 'storno rezervace'], JSON_THROW_ON_ERROR),
            ]),
            array_merge($baseRecord, [
                'key' => 'front-gdpr',
                'page_name' => 'Ochrana osobních údajů',
                'route_name' => 'front.gdpr',
                'path' => '/ochrana-osobnich-udaju',
                'schema_type' => 'WebPage',
                'seo_title' => 'Ochrana osobních údajů | U Sejmona pod hájkem',
                'seo_description' => 'Informace o zpracování osobních údajů v souladu s GDPR. Zjistěte, jak chráníme vaše soukromí v restauraci U Sejmona pod hájkem.',
                'seo_keywords' => json_encode(['GDPR', 'ochrana osobních údajů', 'soukromí', 'U Sejmona pod hájkem'], JSON_THROW_ON_ERROR),
                'aeo_summary' => 'Zásady ochrany osobních údajů restaurace U Sejmona pod hájkem dle nařízení GDPR.',
                'aeo_search_intent' => 'Informační – hledání informací o zpracování osobních údajů.',
                'aeo_entities' => json_encode(['GDPR', 'Ochrana osobních údajů', 'Restaurace U Sejmona pod hájkem'], JSON_THROW_ON_ERROR),
            ]),
            array_merge($baseRecord, [
                'key' => 'front-galleries',
                'page_name' => 'Galerie',
                'route_name' => 'front.galleries',
                'path' => '/galerie',
                'schema_type' => 'WebPage',
                'seo_title' => 'Galerie | U Sejmona pod hájkem',
                'seo_description' => 'Prohlédněte si fotografie z naší restaurace.',
                'seo_keywords' => json_encode(['galerie', 'fotografie', 'restaurace', 'U Sejmona pod hájkem'], JSON_THROW_ON_ERROR),
                'aeo_summary' => 'Fotogalerie restaurace U Sejmona pod hájkem – snímky interiéru.',
                'aeo_search_intent' => 'Informační – prohlížení fotografií restaurace.',
                'aeo_entities' => json_encode(['Galerie', 'Fotografie', 'Restaurace U Sejmona pod hájkem'], JSON_THROW_ON_ERROR),
            ]),
            array_merge($baseRecord, [
                'key' => 'front-cookies',
                'page_name' => 'Zásady cookies',
                'route_name' => 'front.cookies',
                'path' => '/zasady-cookies',
                'schema_type' => 'WebPage',
                'seo_title' => 'Zásady cookies | U Sejmona pod hájkem',
                'seo_description' => 'Informace o používání cookies na webu restaurace U Sejmona pod hájkem. Přečtěte si, jak a proč cookies používáme.',
                'seo_keywords' => json_encode(['cookies', 'zásady cookies', 'soukromí', 'U Sejmona pod hájkem'], JSON_THROW_ON_ERROR),
                'aeo_summary' => 'Zásady používání cookies na webu restaurace U Sejmona pod hájkem.',
                'aeo_search_intent' => 'Informační – hledání informací o cookies.',
                'aeo_entities' => json_encode(['Cookies', 'Zásady cookies', 'Restaurace U Sejmona pod hájkem'], JSON_THROW_ON_ERROR),
            ]),
            array_merge($baseRecord, [
                'key' => 'reservation',
                'page_name' => 'Rezervace',
                'route_name' => 'restaurant.reservation',
                'path' => '/reservation',
                'schema_type' => 'WebPage',
                'seo_title' => 'Rezervace | U Sejmona pod hájkem',
                'seo_description' => 'Zarezervujte si stůl v restauraci U Sejmona pod hájkem. Rychlá a snadná online rezervace na pár kliknutí.',
                'seo_keywords' => json_encode(['rezervace', 'restaurace', 'stůl', 'online rezervace', 'U Sejmona pod hájkem'], JSON_THROW_ON_ERROR),
                'aeo_summary' => 'Online rezervace stolu v restauraci U Sejmona pod hájkem – rychle a pohodlně.',
                'aeo_search_intent' => 'Transakční – rezervace stolu v restauraci.',
                'aeo_entities' => json_encode(['Rezervace', 'Restaurace U Sejmona pod hájkem', 'online rezervace'], JSON_THROW_ON_ERROR),
                'aeo_faq' => json_encode([
                    ['question' => 'Jak mohu zrušit rezervaci?', 'answer' => 'Rezervaci můžete zrušit prostřednictvím odkazu v potvrzovacím e-mailu nebo nás kontaktujte telefonicky.'],
                    ['question' => 'Jak dopředu mohu rezervovat?', 'answer' => 'Rezervaci lze provést dle aktuální dostupnosti uvedené v rezervačním formuláři.'],
                ], JSON_THROW_ON_ERROR),
            ]),
        ];
    }
};
