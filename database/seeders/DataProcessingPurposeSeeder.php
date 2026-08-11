<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Compliance\LegalBasis;
use App\Models\DataProcessingPurpose;
use Illuminate\Database\Seeder;

class DataProcessingPurposeSeeder extends Seeder
{
    /**
     * Canonical processing purposes keyed by stable name.
     *
     * The `name` column serves as the idempotency key so that re-running
     * the seeder updates the canonical record without creating duplicates.
     * Admin-created custom purposes with different names are left untouched.
     *
     * Retention wording uses qualified language ("po dobu nezbytně nutnou"
     * / "in accordance with applicable law") because concrete periods
     * depend on the administrator‑configured CompanyProfile, actual
     * contractual terms, and statutory requirements that the application
     * cannot determine at seed time.
     *
     * Analytics and Marketing purposes are set to Consent because they
     * rely on the visitor's opt-in through the cookie / consent banner.
     */
    private const PURPOSES = [
        [
            'name' => 'Poptávky cateringu, rozvozu a zákaznická komunikace',
            'context' => 'customer_inquiries',
            'description' => 'Zpracování osobních údajů nezbytných pro vyřízení dotazu nebo poptávky služeb Gourmetu, zejména cateringu, rautu, rozvozu nebo jiné zákaznické komunikace. Zahrnuje jméno, kontaktní údaje, obsah zprávy a údaje nutné k přípravě nabídky či odpovědi.',
            'personal_data_categories' => "Identifikační údaje (jméno, příjmení)\nKontaktní údaje (telefon, e-mail)\nObsah poptávky nebo komunikace",
            'legal_basis' => LegalBasis::LegitimateInterest,
            'retention_period' => 'Po dobu nezbytně nutnou pro vyřízení poptávky či komunikace a dále po přiměřenou dobu pro ochranu právních nároků správce.',
            'recipients' => "Správce (provozovatel Gourmetu dle profilu společnosti)\nPoskytovatelé hostingu, e-mailových a komunikačních služeb",
            'third_country_transfer' => null,
            'is_active' => true,
            'priority' => 10,
        ],
        [
            'name' => 'Komunikace se zákazníky',
            'context' => 'customer_communication',
            'description' => 'Zpracování kontaktních údajů pro účely komunikace se zákazníky v souvislosti s jejich dotazy, poptávkami a případnými změnami. Zahrnuje odpovědi na zprávy zaslané prostřednictvím dostupných komunikačních kanálů.',
            'personal_data_categories' => "Kontaktní údaje (telefon, e‑mail)\nObsah komunikace (zprávy, dotazy)",
            'legal_basis' => LegalBasis::LegitimateInterest,
            'retention_period' => 'Po dobu nezbytně nutnou pro vyřízení komunikace a dále po dobu odpovídající oprávněnému zájmu správce na vedení evidence zákaznické komunikace.',
            'recipients' => "Správce (provozovatel restaurace dle profilu společnosti)\nPoskytovatelé komunikačních a e‑mailových služeb",
            'third_country_transfer' => null,
            'is_active' => true,
            'priority' => 20,
        ],
        [
            'name' => 'Plnění právních a účetních povinností',
            'context' => 'legal_accounting',
            'description' => 'Zpracování a uchování údajů nezbytných pro splnění zákonných povinností správce, zejména v oblasti účetnictví, daňové evidence a archivace, a to pouze v rozsahu, v jakém existuje příslušný doklad či transakce. Rozsah a doba uchování se řídí příslušnými právními předpisy (např. zákon o účetnictví, zákon o DPH, občanský zákoník).',
            'personal_data_categories' => "Identifikační údaje\nTransakční údaje (platby, fakturační údaje)\nÚdaje nezbytné pro vedení účetnictví",
            'legal_basis' => LegalBasis::LegalObligation,
            'retention_period' => 'Po dobu stanovenou platnými právními předpisy (typicky 5–10 let dle povahy dokladu).',
            'recipients' => "Správce\nÚčetní a daňoví poradci\nOrgány veřejné správy v rozsahu stanoveném zákonem",
            'third_country_transfer' => null,
            'is_active' => true,
            'priority' => 30,
        ],
        [
            'name' => 'Evidence souhlasů a bezpečnostní logy',
            'context' => 'consent_security',
            'description' => 'Uchování záznamů o udělených souhlasech (cookies, marketing) a zpracování technických bezpečnostních logů pro zajištění integrity, důvěrnosti a dostupnosti systémů. Záznamy slouží jako důkazní prostředek pro případnou kontrolu dozorovým orgánem a k odhalování bezpečnostních incidentů.',
            'personal_data_categories' => "IP adresa (hashovaná)\nUser‑agent (hashovaný)\nČasové razítko souhlasu\nPreference souhlasu",
            'legal_basis' => LegalBasis::LegitimateInterest,
            'retention_period' => 'Po dobu trvání souhlasu a přiměřenou dobu po jeho odvolání či vypršení, nejdéle po dobu stanovenou platnými právními předpisy pro účely obhajoby právních nároků.',
            'recipients' => "Správce\nPoskytovatelé hostingových a bezpečnostních služeb",
            'third_country_transfer' => null,
            'is_active' => true,
            'priority' => 40,
        ],
        [
            'name' => 'Analytika návštěvnosti (pouze se souhlasem)',
            'context' => 'analytics',
            'description' => 'Měření a analýza návštěvnosti webových stránek za účelem zlepšování obsahu a uživatelského komfortu. Zpracování probíhá výhradně na základě souhlasu uděleného prostřednictvím cookie lišty. Bez souhlasu nejsou analytické nástroje spuštěny.',
            'personal_data_categories' => "Online identifikátory (cookie ID, IP adresa)\nÚdaje o chování na webu (navštívené stránky, čas, prohlížeč)",
            'legal_basis' => LegalBasis::Consent,
            'retention_period' => 'Po dobu platnosti souhlasu, maximálně však po dobu stanovenou poskytovatelem analytického nástroje a platnými předpisy.',
            'recipients' => "Poskytovatelé analytických služeb (dle aktivních skriptů v nastavení cookies)\nSprávce",
            'third_country_transfer' => 'Může docházet k předání do třetích zemí v závislosti na zvolených analytických nástrojích. Konkrétní informace jsou uvedeny v zásadách cookies a u jednotlivých skriptů.',
            'is_active' => true,
            'priority' => 50,
        ],
        [
            'name' => 'Marketingové nástroje (pouze se souhlasem)',
            'context' => 'marketing',
            'description' => 'Využití marketingových a reklamních nástrojů pro měření kampaní a personalizaci obsahu. Zpracování probíhá výhradně na základě souhlasu uděleného prostřednictvím cookie lišty. Bez souhlasu nejsou marketingové skripty spuštěny.',
            'personal_data_categories' => "Online identifikátory (cookie ID, IP adresa)\nÚdaje o chování na webu\nÚdaje o interakci s reklamním obsahem",
            'legal_basis' => LegalBasis::Consent,
            'retention_period' => 'Po dobu platnosti souhlasu, maximálně však po dobu stanovenou poskytovatelem marketingového nástroje a platnými předpisy.',
            'recipients' => "Poskytovatelé marketingových a reklamních služeb (dle aktivních skriptů v nastavení cookies)\nSprávce",
            'third_country_transfer' => 'Může docházet k předání do třetích zemí v závislosti na zvolených marketingových nástrojích. Konkrétní informace jsou uvedeny v zásadách cookies a u jednotlivých skriptů.',
            'is_active' => true,
            'priority' => 60,
        ],
    ];

    public function run(): void
    {
        foreach (self::PURPOSES as $purpose) {
            DataProcessingPurpose::query()->updateOrCreate(
                ['name' => $purpose['name']],
                $purpose,
            );
        }
    }
}
