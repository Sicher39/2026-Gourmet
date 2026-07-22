<script setup lang="ts">
import {Link, usePage} from '@inertiajs/vue3'
import {computed} from 'vue'

import FlexSection from '@/front/components/Sections/FlexSection.vue'

interface Props {
    build?: number
    company?: string
}

interface CompanyProfile {
    companyName?: string | null
    companyIdNumber?: string | null
    vatId?: string | null
}

interface FooterLegalDocumentItem {
    title: string
    slug: string
    url: string
    type: string | null
    typeLabel: string | null
    version: string | null
    effectiveFrom: string | null
}

interface InertiaPageProps {
    [key: string]: unknown

    companyProfile?: CompanyProfile
    footerLegalDocuments?: FooterLegalDocumentItem[]
}

const props = withDefaults(defineProps<Props>(), {
    build: 2015
})

const copyright = '– U Sejmona pod hájkem'

const page = usePage<InertiaPageProps>()
const year = computed(() => new Date().getFullYear())

const trimNullable = (value?: string | null): string | null => {
    const trimmedValue = value?.trim()

    return trimmedValue ? trimmedValue : null
}

const companyProfile = computed(() => page.props.companyProfile)
const footerLegalDocuments = computed(() => page.props.footerLegalDocuments ?? [])

const companyName = computed(() => trimNullable(companyProfile.value?.companyName) ?? props.company)
const companyIdNumber = computed(() => trimNullable(companyProfile.value?.companyIdNumber))
const companyVatId = computed(() => trimNullable(companyProfile.value?.vatId))

const footerButtonClass =
        'font-main text-sm font-light tracking-wide text-light transition text-white hover:text-accent underline'
</script>

<template>
    <footer class="block w-full bg-dark pt-10 pb-10 text-light">
        <div
                class="flex justify-end w-full -mt-[70px] smW:-mt-[90px] md:-mt-[100px] lg:-mt-[120px] xl:-mt-[230px]"
        >
            <img src="/img/logo/BK-u-sajmona.svg" class="w-2/12 z-30 mr-20" alt=""/>
        </div>
        <FlexSection>
            <div class="grid w-full grid-cols-1 gap-10 my-10 md:grid-cols-2">
                <div class="block w-full">
                    <h4 class="font-main text-center text-lg text-accent lg:text-left">
                        Dokumenty
                    </h4>
                    <div class="mb-4 flex w-full justify-center lg:justify-start">
                        <div class="h-px w-20 bg-accent"/>
                    </div>

                    <div class="space-y-3">
                        <div
                                v-for="document in footerLegalDocuments"
                                :key="document.slug"
                                class="flex justify-center lg:justify-start"
                        >
                            <Link :href="document.url" :class="footerButtonClass">
                                {{ document.title }}
                            </Link>
                        </div>
                    </div>
                </div>

                <div class="block w-full space-y-3">
                    <div class="block">
                        <h4 class="font-main text-center text-xl text-accent lg:text-left">
                            O nás
                        </h4>
                        <div class="mb-4 flex w-full justify-center lg:justify-start">
                            <div class="h-px w-20 bg-accent"/>
                        </div>
                    </div>

                    <div class="space-y-3 text-sm leading-relaxed">
                        <div
                                class="font-main text-sm font-light tracking-wide text-light flex justify-center gap-3 lg:justify-start"
                        >
                            <span class="w-24 text-white pr-2">Provozovatel:</span>
                            <span class="min-w-0 flex-1 text-white">{{ companyName }}</span>
                        </div>

                        <div
                                v-if="companyIdNumber"
                                class="font-main text-sm font-light tracking-wide text-light flex justify-center gap-3 lg:justify-start"
                        >
                            <span class="w-24 text-white">IČ:</span> <br class="lg:hidden"/>
                            <span class="min-w-0 flex-1 text-white">{{ companyIdNumber }}</span>
                        </div>

                        <div
                                v-if="companyVatId"
                                class="font-main text-sm font-light tracking-wide text-light flex justify-center gap-3 lg:justify-start"
                        >
                            <span class="w-24 text-white">DIČ:</span><br class="lg:hidden"/>
                            <span class="min-w-0 flex-1 text-white">{{ companyVatId }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </FlexSection>

        <p class="mt-10 text-center font-main text-base font-normal text-white md:text-xl">
            <span v-if="props.build === year"
            >&copy; {{ year }} {{ companyName }} {{ copyright }}</span
            >
            <span v-else
            >&copy; {{ props.build }}–{{ year }} {{ companyName }} {{ copyright }}</span
            >
        </p>
    </footer>
</template>
