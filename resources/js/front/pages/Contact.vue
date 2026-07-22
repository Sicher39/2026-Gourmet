<script setup lang="ts">
import MainLayout from '@/front/layouts/MainLayout.vue'
import FullSection from '@/front/components/Sections/FullSection.vue'
import ThreeColumnRevealOverlay from '@/front/components/gsap/ThreeColumnRevealOverlay.vue'
import HeaderSection from '@/front/components/Sections/HeaderSection.vue'
import GsapReveal from '@/front/components/gsap/GsapReveal.vue'
import CompanyInfoItem from '@/front/components/Contacts/CompanyInfoItem.vue'

type CompanyInfo = {
    companyName: string | null
    companyIdNumber: string | null
    vatId: string | null
    bankAccount: string | null
}

const props = defineProps<{
    companyInfo: CompanyInfo | null
}>()

defineOptions({
    layout: MainLayout,
})
</script>

<template>
    <div ref="highlightRoot">
        <div class="relative z-0 h-screen overflow-hidden bg-dark 3xl:max-h-270">
            <div class="pointer-events-none absolute inset-0 z-20">
                <div class="mx-auto grid min-h-full max-w-[1920px] grid-cols-3">
                    <div class="border-r border-accent/20"></div>
                    <div class="border-r border-accent/20"></div>
                </div>
            </div>
            <ThreeColumnRevealOverlay />
        </div>

        <div class="absolute top-10 z-30 pt-10 md:pt-32 lg:pt-20 xl:pt-32">
            <FullSection>
                <HeaderSection>
                    <template #header> Kontakty</template>
                    <template #title>
                        <h2
                            class="text-white items-end text-xl smW:text-lg md:text-3xl lg:text-xl 2xl:text-3xl"
                        >
                            Ozvěte se nám a&nbsp;rádi zodpovíme vaše dotazy. Ať už máte zájem
                            o&nbsp;rezervaci stolu, pořádání akce nebo jen potřebujete více
                            informací – jsme tu pro vás.
                        </h2>
                    </template>
                </HeaderSection>
            </FullSection>
        </div>
    </div>

    <!-- Company identification section -->
    <div v-if="companyInfo" class="flex justify-end mb-20">
        <GsapReveal class="flex w-full md:w-2/3 2xl:w-1/3 bg-dark">
            <FullSection>
                <CompanyInfoItem
                    :company-name="companyInfo.companyName"
                    :company-id-number="companyInfo.companyIdNumber"
                    :vat-id="companyInfo.vatId"
                    :bank-account="companyInfo.bankAccount"
                />
            </FullSection>
        </GsapReveal>
    </div>
</template>
