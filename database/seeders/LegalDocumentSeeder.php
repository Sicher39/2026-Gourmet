<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Compliance\LegalDocumentType;
use App\Models\LegalDocument;
use Illuminate\Database\Seeder;

class LegalDocumentSeeder extends Seeder
{
    /**
     * Idempotently seed canonical legal documents.
     *
     * The `type` column serves as the idempotency key for the two
     * baseline documents (Privacy Policy and Cookie Policy).  A
     * given type should have at most one canonical published record;
     * the seeder updates that record on each run so that content
     * and version remain consistent with the seed data.
     *
     * Document content references the CompanyProfile for actual
     * controller / operator details, which the public page is
     * expected to resolve dynamically.  Placeholders such as
     * "[provozovatel – viz profil společnosti]" indicate where the
     * front end inserts real data from the company-profile record.
     *
     * The content is baseline HTML; no legal advice is intended.
     */
    private const DOCUMENTS = [
        [
            'type' => LegalDocumentType::PrivacyPolicy,
            'title' => 'Zásady ochrany osobních údajů',
            'slug' => 'ochrana-osobnich-udaju',
            'content' => <<<'HTML'
<h2>Správce osobních údajů</h2>
<p>Správcem osobních údajů je provozovatel restaurace, jehož identifikační a kontaktní údaje jsou vedeny v <strong>profilu společnosti</strong> v administraci webu a zobrazují se na veřejné stránce dynamicky. Aktuální údaje naleznete vždy v zápatí webu a na této stránce.</p>

<h2>Jaké údaje zpracováváme</h2>
<p>Zpracováváme pouze údaje nezbytné pro:</p>
<ul>
    <li>vytvoření a správu rezervace stolu (jméno, kontaktní údaje, termín návštěvy),</li>
    <li>komunikaci se zákazníky ohledně jejich rezervací a dotazů,</li>
    <li>plnění zákonných povinností (účetnictví, daňová evidence),</li>
    <li>evidenci udělených souhlasů a zajištění bezpečnosti systémů.</li>
</ul>

<h2>Právní základ zpracování</h2>
<p>Osobní údaje zpracováváme na základě:</p>
<ul>
    <li><strong>plnění smlouvy</strong> – pro vyřízení vaší rezervace,</li>
    <li><strong>oprávněného zájmu</strong> – pro zákaznickou komunikaci, evidenci souhlasů a bezpečnostní logy,</li>
    <li><strong>právní povinnosti</strong> – pro vedení účetnictví a daňové evidence,</li>
    <li><strong>souhlasu</strong> – pro analytické a marketingové nástroje (pouze pokud jste souhlas udělili prostřednictvím cookie lišty).</li>
</ul>

<h2>Komu údaje předáváme</h2>
<p>Osobní údaje předáváme pouze zpracovatelům, kteří zajišťují technický provoz webu, rezervačního systému, hostingových a komunikačních služeb. Bez vašeho souhlasu nepředáváme údaje třetím stranám pro marketingové účely.</p>

<h2>Doba uchování</h2>
<p>Údaje uchováváme po dobu nezbytně nutnou pro daný účel zpracování:</p>
<ul>
    <li>rezervační údaje – po dobu trvání rezervačního vztahu a přiměřenou dobu po jeho ukončení,</li>
    <li>účetní doklady – po dobu stanovenou platnými právními předpisy,</li>
    <li>záznamy souhlasů – po dobu trvání souhlasu a přiměřenou dobu po jeho odvolání,</li>
    <li>bezpečnostní logy – po dobu nezbytnou pro zajištění bezpečnosti systému.</li>
</ul>

<h2>Vaše práva</h2>
<p>V souladu s platnými právními předpisy máte právo:</p>
<ul>
    <li>požadovat přístup k osobním údajům, které o vás zpracováváme,</li>
    <li>požadovat opravu nepřesných nebo neúplných údajů,</li>
    <li>požadovat výmaz údajů, pokud již nejsou potřebné pro účely, pro které byly shromážděny,</li>
    <li>vzést námitku proti zpracování založenému na oprávněném zájmu,</li>
    <li>odvolat souhlas se zpracováním (tím není dotčena zákonnost zpracování před odvoláním),</li>
    <li>podat stížnost u dozorového orgánu (Úřad pro ochranu osobních údajů).</li>
</ul>

<h2>Kontakt</h2>
<p>Pro uplatnění vašich práv nebo dotazy ohledně zpracování osobních údajů nás kontaktujte prostřednictvím kontaktních údajů uvedených v profilu společnosti (zápatí webu).</p>

<h2>Cookies a sledovací nástroje</h2>
<p>Podrobné informace o používaných cookies a sledovacích nástrojích naleznete v samostatném dokumentu <a href="/zasady-cookies">Zásady cookies</a>.</p>

<h2>Platnost a změny</h2>
<p>Tento dokument je platný od data uvedeného v záhlaví stránky. Vyhrazujeme si právo jej průběžně aktualizovat. Aktuální verze je vždy dostupná na této stránce.</p>
HTML,
            'version' => '1.0',
            'effective_from' => '2026-01-01',
            'is_published' => true,
        ],
        [
            'type' => LegalDocumentType::CookiePolicy,
            'title' => 'Zásady používání cookies',
            'slug' => 'zasady-cookies',
            'content' => <<<'HTML'
<h2>Co jsou cookies</h2>
<p>Cookies jsou malé textové soubory, které webové stránky ukládají do vašeho prohlížeče. Slouží k zajištění základních funkcí webu, analýze návštěvnosti a personalizaci obsahu.</p>

<h2>Jaké kategorie cookies používáme</h2>

<h3>Nezbytné cookies</h3>
<p>Tyto cookies jsou nutné pro správné fungování webu, zajištění bezpečnosti a technickou realizaci rezervací. Není možné je vypnout – bez nich by web nefungoval správně. Patří sem například session cookies, CSRF ochrana a technické identifikátory potřebné pro provoz rezervačního systému.</p>
<p><strong>Právní základ:</strong> oprávněný zájem správce na zajištění funkčnosti a bezpečnosti webu.</p>

<h3>Analytické cookies</h3>
<p>Pomáhají nám rozumět tomu, jak návštěvníci web používají – které stránky navštěvují, jak dlouho se na nich zdrží a odkud přicházejí. Tyto informace využíváme ke zlepšování obsahu a uživatelského komfortu.</p>
<p><strong>Právní základ:</strong> váš souhlas udělený prostřednictvím cookie lišty.</p>
<p>Konkrétní analytické nástroje, které aktuálně využíváme, jsou uvedeny v nastavení cookies (tlačítko „Nastavení cookies“). Jejich seznam se může měnit – vždy obsahuje pouze nástroje, které jsou aktivní a vyžadují váš souhlas.</p>

<h3>Marketingové cookies</h3>
<p>Slouží k měření efektivity reklamních kampaní a případné personalizaci reklamního obsahu. Tyto cookies nastavují nástroje třetích stran.</p>
<p><strong>Právní základ:</strong> váš souhlas udělený prostřednictvím cookie lišty.</p>
<p>Konkrétní marketingové nástroje, které aktuálně využíváme, jsou uvedeny v nastavení cookies. Jejich seznam se může měnit.</p>

<h3>Preferenční cookies</h3>
<p>Umožňují webu zapamatovat si vaše volby a nastavení (např. jazykové preference) pro příští návštěvu.</p>
<p><strong>Právní základ:</strong> váš souhlas udělený prostřednictvím cookie lišty.</p>

<h2>Jak můžete cookies spravovat</h2>
<p>Své preference můžete kdykoli změnit kliknutím na odkaz <strong>„Nastavení cookies“</strong> v zápatí webu. V nastavení prohlížeče můžete také cookies blokovat nebo mazat – přesný postup se liší podle používaného prohlížeče. Upozorňujeme však, že blokování nezbytných cookies může ovlivnit funkčnost webu.</p>

<h2>Seznam konkrétních cookies</h2>
<p>Aktuální seznam všech používaných cookies včetně jejich poskytovatelů, účelu a doby platnosti je k dispozici v rozhraní <strong>„Nastavení cookies“</strong> (dostupné přes odkaz v zápatí webu), které zobrazuje vždy aktuální stav dle administrací spravovaných skriptů.</p>

<h2>Kontakt</h2>
<p>V případě dotazů ohledně používání cookies nás kontaktujte prostřednictvím kontaktních údajů uvedených v profilu společnosti.</p>

<h2>Platnost a změny</h2>
<p>Tento dokument je platný od data uvedeného v záhlaví stránky. Můžeme jej průběžně aktualizovat; aktuální verze je vždy dostupná na této stránce.</p>
HTML,
            'version' => '1.0',
            'effective_from' => '2026-01-01',
            'is_published' => true,
        ],
    ];

    public function run(): void
    {
        foreach (self::DOCUMENTS as $document) {
            LegalDocument::query()->updateOrCreate(
                ['type' => $document['type']],
                $document,
            );
        }
    }
}
