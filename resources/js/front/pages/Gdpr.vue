<script setup lang="ts">
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

import BasicGdpr from '@/front/components/Gdpr/BasicGdpr.vue'
import FlexSection from '@/front/components/Sections/FlexSection.vue'
import MainLayout from '@/front/layouts/MainLayout.vue'

interface CompanyProfile {
  companyName?: string | null
  companyIdNumber?: string | null
  address?: string | null
  street?: string | null
  city?: string | null
  zip?: string | null
  country?: string | null
  email?: string | null
  phone?: string | null
  gdprEffectiveDate?: string | null
}

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

interface InertiaPageProps {
  [key: string]: unknown

  companyProfile?: CompanyProfile
  processingPurposes?: ProcessingPurpose[]
  technicalCookies?: TechnicalCookie[]
}

const page = usePage<InertiaPageProps>()

const companyProfile = computed(() => page.props.companyProfile ?? {})
const processingPurposes = computed(() => page.props.processingPurposes ?? [])
const technicalCookies = computed(() => page.props.technicalCookies ?? [])

const companyAddress = computed(() => {
  const street = companyProfile.value.street?.trim() || null
  const zip = companyProfile.value.zip?.trim() || null
  const city = companyProfile.value.city?.trim() || null
  const country = companyProfile.value.country?.trim() || null

  return [street, [zip, city].filter(Boolean).join(' '), country].filter(Boolean).join(', ')
})

defineOptions({
  layout: MainLayout
})
</script>

<template>
  <div class="block w-full bg-dark">
    <FlexSection>
      <div class="flex w-full justify-center">
        <h1 class="text-accent pt-44 pb-20 text-center text-4xl md:text-7xl">
          Podmínky ochrany <br class="hidden lg:block"> osobních údajů
        </h1>
      </div>
    </FlexSection>
  </div>

  <FlexSection>
    <BasicGdpr
      :administrator="companyProfile.companyName || ''"
      :company-id="companyProfile.companyIdNumber || ''"
      :address="companyAddress || ''"
      :email="companyProfile.email || ''"
      :tel="companyProfile.phone || ''"
      :date="companyProfile.gdprEffectiveDate || ''"
      :processing-purposes="processingPurposes"
      :technical-cookies="technicalCookies"
    />
  </FlexSection>
</template>
