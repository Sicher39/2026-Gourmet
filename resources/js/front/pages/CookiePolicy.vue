<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { usePage } from '@inertiajs/vue3'
import MainLayout from '@/front/layouts/MainLayout.vue'
import FlexSection from '@/front/components/Sections/FlexSection.vue'
import type { CookieConfigPayload, TrackingScriptPayload } from '@/front/types/cookies'

interface SharedCompanyProfile {
  companyName?: string | null
}

defineOptions({ layout: MainLayout })

const page = usePage<{ companyProfile?: SharedCompanyProfile }>()
const sharedCompanyProfile = computed(() => page.props.companyProfile ?? {})
const operatorName = computed(() => sharedCompanyProfile.value.companyName?.trim() || null)

const config = ref<CookieConfigPayload | null>(null)

const categoryLabels: Record<string, string> = {
    necessary: 'Nezbytné\u00A0(technické)',
    analytics: 'Analytické',
    marketing: 'Marketingové',
    preferences: 'Preferenční'
}

const groupedScripts = computed<Record<string, TrackingScriptPayload[]>>(() => {
    const groups: Record<string, TrackingScriptPayload[]> = {
        necessary: [],
        analytics: [],
        marketing: [],
        preferences: []
    }

    for (const script of config.value?.trackingScripts ?? []) {
        groups[script.category] = groups[script.category] ?? []
        groups[script.category].push(script)
    }

    return groups
})

const currentYear = new Date().getFullYear()

onMounted(async () => {
    try {
        const response = await fetch('/api/compliance/cookie-config', {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin'
        })

        if (!response.ok) {
            config.value = null
            return
        }

        config.value = (await response.json()) as CookieConfigPayload
    } catch {
        config.value = null
    }
})
</script>

<template>
    <div class="block w-full h-20 bg-dark/5"></div>
    <FlexSection>
        <div class="w-full bg-dark/5-green py-20 font-main text-dark md:py-28 px-20">
            <h1 class="font-main text-4xl font-bold text-dark md:text-6xl">
                Zásady používání cookies
            </h1>

            <p class="mt-6 text-lg leading-relaxed text-dark">
                <template v-if="operatorName">
                    Tento dokument vysvětluje, jak web&nbsp;{{ operatorName }} používá cookies
                    a&nbsp;podobné technologie, jaké kategorie nástrojů mohou být aktivní a&nbsp;jak je
                    případně možné upravit souhlas.
                </template>
                <template v-else>
                    Tento dokument vysvětluje, jak tento web používá cookies
                    a&nbsp;podobné technologie, jaké kategorie nástrojů mohou být aktivní a&nbsp;jak je
                    případně možné upravit souhlas.
                </template>
            </p>

            <div class="mt-10 rounded-3xl border border-accent-green p-6">
                <h2 class="font-main text-2xl font-bold text-dark">
                    Aktuální režim na&nbsp;tomto webu
                </h2>
                <p class="mt-3 text-lg leading-relaxed text-dark">
                    <span v-if="config?.requiresCookieConsent">
                        Na&nbsp;webu je aktivní alespoň jeden volitelný nástroj (analytický,
                        marketingový nebo preferenční), který vyžaduje souhlas. Proto se může
                        zobrazit cookie lišta a&nbsp;dialog pro nastavení.
                    </span>
                    <span v-else>
                        Aktuálně používáme pouze nezbytné technické cookies potřebné pro provoz
                        webu. V&nbsp;takovém režimu se nezobrazuje dialog pro správu souhlasu,
                        protože pro technické cookies není souhlas vyžadován.
                    </span>
                </p>
            </div>

            <div class="mt-10 space-y-8 text-lg leading-relaxed">
                <section>
                    <h2 class="font-main text-2xl font-bold text-dark">Co jsou cookies</h2>
                    <p class="mt-3 text-dark">
                        Cookies jsou malé textové soubory, které se ukládají v&nbsp;prohlížeči
                        návštěvníka. Pomáhají zajistit funkčnost webu, bezpečnost, případně měření
                        návštěvnosti nebo personalizaci obsahu podle zvoleného režimu.
                    </p>
                </section>

                <section>
                    <h2 class="font-main text-2xl font-bold text-dark">Kdo web provozuje</h2>
                    <p v-if="operatorName" class="mt-3 text-dark">
                        Správcem tohoto webu je {{ operatorName }}. Informace
                        o&nbsp;zpracování osobních údajů naleznete v&nbsp;dokumentu
                        <a href="/ochrana-osobnich-udaju" class="text-accent-green underline"
                            >Ochrana osobních údajů</a
                        >.
                    </p>
                    <p v-else class="mt-3 text-dark">
                        Identifikační údaje provozovatele webu nejsou aktuálně nastaveny. Podrobnosti
                        o&nbsp;zpracování osobních údajů naleznete v&nbsp;dokumentu
                        <a href="/ochrana-osobnich-udaju" class="text-accent-green underline"
                            >Ochrana osobních údajů</a
                        >.
                    </p>
                </section>

                <section>
                    <h2 class="font-main text-2xl font-bold text-dark">
                        Nezbytné technické cookies
                    </h2>
                    <p class="mt-3 text-dark">
                        Tyto cookies jsou nutné pro základní fungování webu (např.&nbsp;zabezpečení,
                        stabilitu nebo správné načítání obsahu). Bez nich by web nemusel fungovat
                        správně. Pro tuto kategorii se souhlas nevyžaduje.
                    </p>
                </section>

                <section>
                    <h2 class="font-main text-2xl font-bold text-dark">
                        Volitelné kategorie pouze po&nbsp;souhlasu
                    </h2>
                    <p class="mt-3 text-dark">
                        Pokud jsou na&nbsp;webu aktivovány analytické, marketingové nebo preferenční
                        nástroje, spouštějí se až po&nbsp;udělení souhlasu v&nbsp;cookie liště nebo
                        v&nbsp;nastavení cookies.
                    </p>
                </section>

                <section>
                    <h2 class="font-main text-2xl font-bold text-dark">
                        Jak změnit nebo odvolat souhlas
                    </h2>
                    <p class="mt-3 text-dark">
                        Když jsou povoleny nástroje vyžadující souhlas, můžete volbu kdykoli změnit
                        v&nbsp;dialogu nastavení cookies. Pokud web používá pouze technické cookies,
                        dialog nastavení se nezobrazuje, protože není potřeba.
                    </p>
                </section>
            </div>

            <div class="mt-12">
                <h2 class="font-main text-2xl font-bold text-dark">
                    Aktivní nástroje podle kategorií
                </h2>
                <p class="mt-3 text-lg leading-relaxed text-dark">
                    Níže je aktuální přehled nástrojů načtený z&nbsp;konfigurace webu.
                </p>

                <div class="mt-6 space-y-8">
                    <section
                        v-for="(scripts, category) in groupedScripts"
                        :key="category"
                        class="rounded-3xl border border-accent-green p-6"
                    >
                        <h3 class="font-main text-xl font-bold text-dark">
                            {{ categoryLabels[category] ?? category }}
                        </h3>

                        <p v-if="scripts.length === 0" class="mt-3 text-dark/80">
                            V&nbsp;této kategorii není aktuálně zapnutý žádný nástroj.
                        </p>

                        <div v-else class="mt-4 grid gap-4 md:grid-cols-2">
                            <article
                                v-for="script in scripts"
                                :key="script.id"
                                class="rounded-2xl border border-accent-green bg-dark/5 p-4"
                            >
                                <h4 class="font-main text-2xl font-bold text-dark">
                                    {{ script.name }}
                                </h4>
                                <p v-if="script.description" class="mt-2 text-dark/80">
                                    {{ script.description }}
                                </p>
                                <dl class="mt-3 space-y-2 text-sm">
                                    <div>
                                        <dt class="font-main font-bold text-dark">Poskytovatel</dt>
                                        <dd>
                                            {{
                                                script.providerName ??
                                                script.provider ??
                                                'Neuvedeno'
                                            }}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="font-main font-bold text-dark">
                                            Vyžaduje souhlas
                                        </dt>
                                        <dd>{{ script.requiresConsent ? 'Ano' : 'Ne' }}</dd>
                                    </div>
                                </dl>
                                <a
                                    v-if="script.providerPrivacyUrl"
                                    :href="script.providerPrivacyUrl"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="mt-3 inline-block text-accent-green underline"
                                >
                                    Zásady soukromí poskytovatele
                                </a>
                            </article>
                        </div>
                    </section>
                </div>
            </div>

            <p class="mt-12 text-sm text-dark/70">
                Účinnost dokumentu: {{ currentYear }}. Tento přehled může být průběžně aktualizován
                podle změn používaných nástrojů.
            </p>
        </div>
    </FlexSection>
</template>
