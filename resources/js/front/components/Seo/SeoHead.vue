<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

type StructuredData = Record<string, unknown> | Record<string, unknown>[] | null

type SocialProfiles = {
  facebook?: string | null
  instagram?: string | null
  linkedin?: string | null
  youtube?: string | null
}

export interface SeoPayload {
  title?: string | null
  description?: string | null
  keywords?: string[] | string | null
  canonical?: string | null
  robots?: string | null
  ogTitle?: string | null
  ogDescription?: string | null
  ogImage?: string | null
  ogType?: string | null
  ogUrl?: string | null
  ogLocale?: string | null
  ogSiteName?: string | null
  twitterTitle?: string | null
  twitterDescription?: string | null
  twitterImage?: string | null
  twitterCard?: string | null
  socialProfiles?: SocialProfiles | null
  structuredData?: StructuredData
}

export type SeoProps = SeoPayload

const DEFAULT_SITE_NAME = 'Gourmet Restaurant'
const DEFAULT_OG_IMAGE = '/img/logo/gourmet-logo.svg'
const DEFAULT_TITLE = 'Gourmet Restaurant Brno | Ponávka a U Vaňkovky'
const DEFAULT_OG_LOCALE = 'cs_CZ'

const props = defineProps<{
  seo?: SeoPayload | null
}>()

const page = usePage<{ seo?: SeoPayload | null }>()

const normalizeText = (value?: string | null): string | null => {
  if (typeof value !== 'string') {
    return null
  }

  const normalized = value.trim()

  return normalized === '' ? null : normalized
}

const normalizeKeywords = (value: SeoPayload['keywords']): SeoPayload['keywords'] => {
  if (typeof value === 'string') {
    return normalizeText(value)
  }

  if (!Array.isArray(value)) {
    return null
  }

  const normalizedKeywords = value
    .map((item) => normalizeText(item))
    .filter((item): item is string => item !== null)

  return normalizedKeywords.length > 0 ? normalizedKeywords : null
}

const resolvedSeoPayload = computed<SeoPayload | null>(() => props.seo ?? page.props.seo ?? null)

const hasExplicitTwitterMeta = computed<boolean>(() => {
  const resolvedSeo = resolvedSeoPayload.value

  return [resolvedSeo?.twitterTitle, resolvedSeo?.twitterDescription, resolvedSeo?.twitterImage, resolvedSeo?.twitterCard]
    .some((value) => normalizeText(value) !== null)
})

const seo = computed<SeoPayload>(() => {
  const resolvedSeo = resolvedSeoPayload.value
  const title = normalizeText(resolvedSeo?.title) ?? DEFAULT_TITLE
  const description = normalizeText(resolvedSeo?.description)
  const canonical = normalizeText(resolvedSeo?.canonical)

  const ogTitle = normalizeText(resolvedSeo?.ogTitle) ?? title
  const ogDescription = normalizeText(resolvedSeo?.ogDescription) ?? description
  const ogImage = normalizeText(resolvedSeo?.ogImage) ?? DEFAULT_OG_IMAGE
  const ogUrl = normalizeText(resolvedSeo?.ogUrl) ?? canonical

  const twitterEnabled = hasExplicitTwitterMeta.value

  return {
    title,
    description,
    keywords: normalizeKeywords(resolvedSeo?.keywords ?? null),
    canonical,
    robots: normalizeText(resolvedSeo?.robots) ?? 'index, follow',
    ogTitle,
    ogDescription,
    ogImage,
    ogType: normalizeText(resolvedSeo?.ogType) ?? 'website',
    ogUrl,
    ogLocale: normalizeText(resolvedSeo?.ogLocale) ?? DEFAULT_OG_LOCALE,
    ogSiteName: normalizeText(resolvedSeo?.ogSiteName) ?? DEFAULT_SITE_NAME,
    twitterTitle: twitterEnabled ? (normalizeText(resolvedSeo?.twitterTitle) ?? ogTitle) : null,
    twitterDescription: twitterEnabled
      ? (normalizeText(resolvedSeo?.twitterDescription) ?? ogDescription ?? description)
      : null,
    twitterImage: twitterEnabled ? (normalizeText(resolvedSeo?.twitterImage) ?? ogImage) : null,
    twitterCard: twitterEnabled ? (normalizeText(resolvedSeo?.twitterCard) ?? 'summary_large_image') : null,
    socialProfiles: resolvedSeo?.socialProfiles ?? null,
    structuredData: resolvedSeo?.structuredData ?? null,
  }
})

const hasOpenGraphMeta = computed<boolean>(() => {
  return [seo.value.ogTitle, seo.value.ogDescription, seo.value.ogUrl, seo.value.ogImage]
    .some((value) => normalizeText(value) !== null)
})

const hasTwitterMeta = computed<boolean>(() => {
  return [seo.value.twitterTitle, seo.value.twitterDescription, seo.value.twitterImage]
    .some((value) => normalizeText(value) !== null)
})

const keywordsContent = computed(() => {
  const keywords = seo.value.keywords

  if (!keywords) {
    return null
  }

  return Array.isArray(keywords) ? keywords.join(', ') : keywords
})

const jsonLd = computed<string | null>(() => {
  if (!seo.value.structuredData) {
    return null
  }

  return JSON.stringify(seo.value.structuredData)
})
</script>

<template>
  <Head>
    <title>{{ seo.title }}</title>

    <meta v-if="seo.description" head-key="description" name="description" :content="seo.description" />
    <meta v-if="keywordsContent" head-key="keywords" name="keywords" :content="keywordsContent" />
    <meta v-if="seo.robots" head-key="robots" name="robots" :content="seo.robots" />
    <link v-if="seo.canonical" head-key="canonical" rel="canonical" :href="seo.canonical" />

    <meta v-if="hasOpenGraphMeta && seo.ogTitle" head-key="og:title" property="og:title" :content="seo.ogTitle" />
    <meta
      v-if="hasOpenGraphMeta && seo.ogDescription"
      head-key="og:description"
      property="og:description"
      :content="seo.ogDescription"
    />
    <meta v-if="hasOpenGraphMeta && seo.ogType" head-key="og:type" property="og:type" :content="seo.ogType" />
    <meta v-if="hasOpenGraphMeta && seo.ogUrl" head-key="og:url" property="og:url" :content="seo.ogUrl" />
    <meta v-if="hasOpenGraphMeta && seo.ogImage" head-key="og:image" property="og:image" :content="seo.ogImage" />
    <meta
      v-if="hasOpenGraphMeta && seo.ogSiteName"
      head-key="og:site_name"
      property="og:site_name"
      :content="seo.ogSiteName"
    />
    <meta
      v-if="hasOpenGraphMeta && seo.ogLocale"
      head-key="og:locale"
      property="og:locale"
      :content="seo.ogLocale"
    />

    <meta v-if="hasTwitterMeta && seo.twitterCard" head-key="twitter:card" name="twitter:card" :content="seo.twitterCard" />
    <meta v-if="hasTwitterMeta && seo.twitterTitle" head-key="twitter:title" name="twitter:title" :content="seo.twitterTitle" />
    <meta
      v-if="hasTwitterMeta && seo.twitterDescription"
      head-key="twitter:description"
      name="twitter:description"
      :content="seo.twitterDescription"
    />
    <meta v-if="hasTwitterMeta && seo.twitterImage" head-key="twitter:image" name="twitter:image" :content="seo.twitterImage" />

    <component
      :is="'script'"
      v-if="jsonLd"
      head-key="structured-data"
      type="application/ld+json"
      :innerHTML="jsonLd"
    />
  </Head>
</template>
