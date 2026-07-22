<script setup lang="ts">
import { computed } from 'vue'

interface ProcessingPurpose {
    id: number
    name: string
    context: string | null
    description: string | null
    personalDataCategories: string | null
    legalBasis: string | null
    retentionPeriod: string | null
    recipients: string | null
    thirdCountryTransfer: string | null
}

interface TechnicalCookie {
    id: number
    name: string
    providerName: string | null
    description: string | null
    providerPrivacyUrl: string | null
    requiresConsent: boolean
}

const props = withDefaults(
    defineProps<{
        administrator?: string
        companyId?: string
        address?: string
        adress?: string
        email?: string
        tel?: string
        date?: string
        processingPurposes?: ProcessingPurpose[]
        technicalCookies?: TechnicalCookie[]
    }>(),
    {
        administrator: '',
        companyId: '',
        address: '',
        adress: '',
        email: '',
        tel: '',
        date: '',
        processingPurposes: () => [],
        technicalCookies: () => []
    }
)

const resolvedAddress = computed(() => props.address || props.adress)

const contextLabels: Record<string, string> = {
    restaurant_reservation: 'Rezervace a plnění smluv',
    customer_communication: 'Komunikace se zákazníky',
    legal_accounting: 'Právní a účetní povinnosti',
    consent_security: 'Souhlasy a bezpečnost',
    analytics: 'Volitelná analytika',
    marketing: 'Volitelný marketing',
    other: 'Ostatní účely'
}

const groupedProcessingPurposes = computed(() => {
    return props.processingPurposes.reduce<Record<string, ProcessingPurpose[]>>(
        (groups, purpose) => {
            const context = purpose.context || 'other'
            groups[context] = groups[context] ?? []
            groups[context].push(purpose)

            return groups
        },
        {}
    )
})

const hasAdministrator = computed(() => (props.administrator?.trim() ?? '') !== '')

const hasAnyContactData = computed(
    () => !!(resolvedAddress.value || props.email || props.tel),
)
const hasProcessingPurposes = computed(() => props.processingPurposes.length > 0)
const hasTechnicalCookies = computed(() => props.technicalCookies.length > 0)
</script>

<template>
    <div class="block w-full bg-dark/5-green pb-30 pt-30">
        <div class="flex justify-center w-full">
            <div
                class="font-main font-normal text-dark text-lg block w-full lg:w-10/12 xl:w-8/12 space-y-10"
            >
                <section>
                    <h2 class="font-main font-bold text-xl lg:text-2xl text-center text-dark">
                        I. Základní ustanovení
                    </h2>
                    <ol class="list-decimal pl-6 space-y-4 mt-4">
                        <li v-if="hasAdministrator">
                            Správcem osobních údajů podle čl. 4 bodu 7 nařízení Evropského
                            parlamentu a Rady (EU) 2016/679 o ochraně fyzických osob v souvislosti
                            se zpracováním osobních údajů a o volném pohybu těchto údajů (<span
                                class="font-bold"
                                >„GDPR“</span
                            >) je <span class="font-bold">{{ administrator }}</span
                            ><template v-if="companyId"
                                >, IČ <span class="font-bold">{{ companyId }}</span></template
                            ><template v-if="resolvedAddress"
                                >, se sídlem <span class="font-bold">{{ resolvedAddress }}</span></template
                            > (dále
                            jen <span class="font-bold">„správce“</span>).
                        </li>
                        <li v-else class="text-dark/80">
                            Identifikační údaje správce osobních údajů nejsou aktuálně dostupné.
                            Pro ověření správce nás prosím kontaktujte prostřednictvím kontaktních
                            údajů uvedených na webu.
                        </li>
                        <li v-if="hasAnyContactData">
                            Kontaktní údaje správce:
                            <ul class="list-disc pl-6 mt-2 space-y-1">
                                <li v-if="resolvedAddress">
                                    adresa: <span class="font-bold">{{ resolvedAddress }}</span>
                                </li>
                                <li v-if="email">
                                    e-mail: <span class="font-bold">{{ email }}</span>
                                </li>
                                <li v-if="tel">
                                    telefon: <span class="font-bold">{{ tel }}</span>
                                </li>
                            </ul>
                        </li>
                        <li>
                            Osobními údaji se rozumí veškeré informace o identifikované nebo
                            identifikovatelné fyzické osobě.
                        </li>
                    </ol>
                </section>

                <section>
                    <h2 class="font-main font-bold text-xl lg:text-2xl text-center text-dark">
                        II. Zdroje a kategorie zpracovávaných osobních údajů
                    </h2>
                    <ol class="list-decimal pl-6 space-y-4 mt-4">
                        <li>
                            Zpracováváme osobní údaje, které nám poskytnete zejména prostřednictvím
                            rezervačního formuláře, e-mailem, telefonicky nebo
                            při následné komunikaci ohledně rezervace.
                        </li>
                        <li>
                            Typicky jde o identifikační a kontaktní údaje (jméno, příjmení,
                            e-mail, telefon), údaje o rezervaci (termín, počet hostů),
                            fakturační údaje (pokud jsou poskytnuty a vyžadovány),
                            obsah komunikace a technické údaje vznikající při
                            používání webu.
                        </li>
                        <li>
                            Pokud jsou na webu zapnuté volitelné analytické nebo marketingové
                            nástroje, zpracováváme příslušné technické identifikátory pouze na
                            základě vašeho souhlasu uděleného v cookie liště.
                        </li>
                    </ol>
                </section>

                <section>
                    <h2 class="font-main font-bold text-xl lg:text-2xl text-center text-dark">
                        III. Účely zpracování a právní základy
                    </h2>
                    <p class="mt-4 text-dark">
                        Osobní údaje zpracováváme pouze v rozsahu nezbytném pro níže uvedené účely.
                        Přehled účelů je načítán z administrace webu, aby odpovídal aktuálním
                        rezervačním procesům a používaným technickým nástrojům.
                    </p>

                    <div v-if="hasProcessingPurposes" class="mt-6 space-y-6">
                        <section
                            v-for="(purposes, context) in groupedProcessingPurposes"
                            :key="context"
                            class="rounded-3xl border border-accent-green p-5"
                        >
                            <h3 class="font-main text-xl font-bold text-dark">
                                {{ contextLabels[String(context)] ?? String(context) }}
                            </h3>

                            <div class="mt-4 space-y-4">
                                <article
                                    v-for="purpose in purposes"
                                    :key="purpose.id"
                                    class="rounded-2xl bg-dark/5 p-4"
                                >
                                    <h4 class="font-main text-lg font-bold text-dark">
                                        {{ purpose.name }}
                                    </h4>
                                    <p v-if="purpose.description" class="mt-2 text-dark/90">
                                        {{ purpose.description }}
                                    </p>
                                    <dl class="mt-3 grid gap-3 text-base md:grid-cols-2">
                                        <div v-if="purpose.legalBasis">
                                            <dt class="font-bold text-dark">Právní základ</dt>
                                            <dd>{{ purpose.legalBasis }}</dd>
                                        </div>
                                        <div v-if="purpose.retentionPeriod">
                                            <dt class="font-bold text-dark">Doba uchování</dt>
                                            <dd>{{ purpose.retentionPeriod }}</dd>
                                        </div>
                                        <div
                                            v-if="purpose.personalDataCategories"
                                            class="md:col-span-2"
                                        >
                                            <dt class="font-bold text-dark">Kategorie údajů</dt>
                                            <dd>{{ purpose.personalDataCategories }}</dd>
                                        </div>
                                        <div v-if="purpose.recipients" class="md:col-span-2">
                                            <dt class="font-bold text-dark">Příjemci</dt>
                                            <dd>{{ purpose.recipients }}</dd>
                                        </div>
                                        <div
                                            v-if="purpose.thirdCountryTransfer"
                                            class="md:col-span-2"
                                        >
                                            <dt class="font-bold text-dark">
                                                Předání mimo EU/EHP
                                            </dt>
                                            <dd>{{ purpose.thirdCountryTransfer }}</dd>
                                        </div>
                                    </dl>
                                </article>
                            </div>
                        </section>
                    </div>

                    <ol v-else class="list-decimal pl-6 space-y-4 mt-4">
                        <li>
                            Rezervace zpracováváme pro jednání o smlouvě a její plnění.
                        </li>
                        <li>Účetní a daňové údaje uchováváme z důvodu zákonných povinností.</li>
                        <li>
                            Zákaznickou komunikaci vedeme na základě oprávněného zájmu a
                            volitelné nástroje používáme jen se souhlasem.
                        </li>
                    </ol>
                </section>

                <section>
                    <h2 class="font-main font-bold text-xl lg:text-2xl text-center text-dark">
                        IV. Cookies a technické nebo volitelné nástroje
                    </h2>
                    <ol class="list-decimal pl-6 space-y-4 mt-4">
                        <li>
                            Web používá nezbytné technické cookies a obdobné technologie potřebné
                            pro bezpečný provoz webu, ochranu formulářů, uložení nastavení cookies a
                            správné fungování aplikace.
                        </li>
                        <li>
                            Analytické, marketingové nebo preferenční nástroje používáme pouze
                            tehdy, pokud jsou zapnuté v administraci webu a návštěvník udělil
                            příslušný souhlas.
                        </li>
                        <li>
                            Souhlas můžete kdykoli změnit nebo odvolat prostřednictvím nastavení
                            cookies, pokud je na webu dostupné. Detailní informace najdete v
                            zásadách používání cookies.
                        </li>
                    </ol>

                    <div
                        v-if="hasTechnicalCookies"
                        class="mt-6 rounded-3xl border border-accent-green p-5"
                    >
                        <h3 class="font-main text-xl font-bold text-dark">
                            Aktuální technické cookies a technické služby
                        </h3>
                        <div class="mt-4 space-y-4">
                            <article
                                v-for="cookie in props.technicalCookies"
                                :key="cookie.id"
                                class="rounded-2xl bg-dark/5 p-4"
                            >
                                <h4 class="font-main text-lg font-bold text-dark">
                                    {{ cookie.name }}
                                </h4>
                                <p v-if="cookie.providerName" class="mt-1 text-sm text-dark/75">
                                    Poskytovatel: {{ cookie.providerName }}
                                </p>
                                <p v-if="cookie.description" class="mt-2 text-dark/90">
                                    {{ cookie.description }}
                                </p>
                                <p class="mt-2 text-sm text-dark/75">
                                    Vyžaduje souhlas: {{ cookie.requiresConsent ? 'ano' : 'ne' }}
                                </p>
                                <a
                                    v-if="cookie.providerPrivacyUrl"
                                    :href="cookie.providerPrivacyUrl"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="mt-2 inline-block text-accent underline"
                                >
                                    Zásady soukromí poskytovatele
                                </a>
                            </article>
                        </div>
                    </div>
                </section>

                <section>
                    <h2 class="font-main font-bold text-xl lg:text-2xl text-center text-dark">
                        V. Příjemci osobních údajů a zpracovatelé
                    </h2>
                    <ol class="list-decimal pl-6 space-y-4 mt-4">
                        <li>
                            Osobní údaje mohou být předány pouze v nezbytném rozsahu osobám a
                            službám, které správci pomáhají s provozem webu, komunikací se
                            zákazníky, účetnictvím, právní agendou nebo technickou správou.
                        </li>
                        <li>
                            Typicky jde o poskytovatele hostingu, e-mailových služeb, správce webu,
                            účetní a daňové poradce, právní poradce, případně poskytovatele
                            nástrojů, které jsou výslovně uvedeny v zásadách cookies.
                        </li>
                        <li>
                            Orgánům veřejné moci předáváme údaje pouze v případech, kdy nám to
                            ukládá právní předpis nebo je to nezbytné pro ochranu právních nároků.
                        </li>
                    </ol>
                </section>

                <section>
                    <h2 class="font-main font-bold text-xl lg:text-2xl text-center text-dark">
                        VI. Předávání údajů mimo EU/EHP
                    </h2>
                    <ol class="list-decimal pl-6 space-y-4 mt-4">
                        <li>
                            Standardně neusilujeme o předávání osobních údajů mimo Evropskou unii
                            nebo Evropský hospodářský prostor.
                        </li>
                        <li>
                            Pokud by k předání mimo EU/EHP došlo u konkrétního technického,
                            analytického nebo marketingového nástroje, bude uvedeno u příslušného
                            účelu nebo v zásadách cookies a bude probíhat pouze při splnění podmínek
                            GDPR, zejména na základě odpovídajících smluvních a bezpečnostních
                            záruk.
                        </li>
                    </ol>
                </section>

                <section>
                    <h2 class="font-main font-bold text-xl lg:text-2xl text-center text-dark">
                        VII. Doba uchovávání
                    </h2>
                    <ol class="list-decimal pl-6 space-y-4 mt-4">
                        <li>
                            Údaje uchováváme po dobu nezbytnou pro splnění účelu, pro který byly
                            získány.
                        </li>
                        <li>
                            Rezervační údaje a běžnou komunikaci uchováváme po dobu vyřízení a přiměřeně
                            poté pro navazující komunikaci a ochranu právních nároků.
                        </li>
                        <li>
                            Účetní a daňové doklady uchováváme po dobu stanovenou právními předpisy.
                        </li>
                        <li>
                            Souhlasy, odmítnutí a nastavení cookies uchováváme po dobu potřebnou k
                            doložení splnění právních povinností.
                        </li>
                    </ol>
                </section>

                <section>
                    <h2 class="font-main font-bold text-xl lg:text-2xl text-center text-dark">
                        VIII. Vaše práva
                    </h2>
                    <ol class="list-decimal pl-6 space-y-4 mt-4">
                        <li>
                            Máte právo požadovat přístup ke svým osobním údajům, jejich opravu nebo
                            doplnění.
                        </li>
                        <li>
                            Za podmínek stanovených GDPR máte právo na výmaz, omezení zpracování a
                            přenositelnost údajů.
                        </li>
                        <li>
                            Máte právo vznést námitku proti zpracování založenému na oprávněném
                            zájmu správce.
                        </li>
                        <li>
                            Pokud zpracování probíhá na základě souhlasu, můžete souhlas kdykoli
                            odvolat bez vlivu na zákonnost předchozího zpracování.
                        </li>
                        <li>
                            Domníváte-li se, že dochází k porušení GDPR, můžete podat stížnost u
                            Úřadu pro ochranu osobních údajů.
                        </li>
                    </ol>
                </section>

                <section>
                    <h2 class="font-main font-bold text-xl lg:text-2xl text-center text-dark">
                        IX. Zabezpečení a závěrečná ustanovení
                    </h2>
                    <ol class="list-decimal pl-6 space-y-4 mt-4">
                        <li>
                            Správce přijal vhodná technická a organizační opatření k zabezpečení
                            osobních údajů.
                        </li>
                        <li>
                            Přístup k osobním údajům mají pouze osoby, které jej potřebují pro
                            plnění svých úkolů nebo zákonných povinností.
                        </li>
                        <li>
                            Správce je oprávněn tyto podmínky aktualizovat. Aktuální verze je vždy
                            zveřejněna na této stránce.
                        </li>
                    </ol>
                </section>

                <p v-if="date" class="font-main mt-10 font-bold">
                    Tyto podmínky nabývají účinnosti dnem {{ date }}.
                </p>
            </div>
        </div>
    </div>
</template>
