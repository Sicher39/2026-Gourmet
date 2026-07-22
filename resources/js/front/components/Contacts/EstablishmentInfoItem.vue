<script setup lang="ts">
import { computed } from 'vue'

const props = withDefaults(
    defineProps<{
        businessName?: string | null
        street?: string | null
        city?: string | null
        zipCode?: string | null
        country?: string | null
        email?: string | null
        phone?: string | null
    }>(),
    {
        businessName: null,
        street: null,
        city: null,
        zipCode: null,
        country: null,
        email: null,
        phone: null,
    }
)

const fullAddress = computed((): string => {
    const cityLine = [props.zipCode, props.city].filter(Boolean).join(' ')

    return [props.street, cityLine, props.country].filter(Boolean).join(', ')
})

const escapeHtml = (value: string): string =>
    value
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;')

const encodeEmailEntities = (value: string): string => escapeHtml(value).replaceAll('@', '&#64;')

const obfuscatedEmailLink = computed((): string => {
    if (!props.email) {
        return ''
    }

    const email = encodeEmailEntities(props.email)

    return `<a href="mai&#108;&#116;&#111;&#58;${email}">email: ${email}</a>`
})
</script>

<template>
    <div class="block">
        <h5 v-if="props.businessName" class="text-xl font-bold text-accent-green">
            {{ props.businessName }}
        </h5>
        <div class="mb-2 mt-1 w-10 border-b border-white" />

        <p v-if="fullAddress">adresa: {{ fullAddress }}</p>
        <p v-if="props.email" v-html="obfuscatedEmailLink" />
        <a v-if="props.phone" :href="`tel:${props.phone}`">
            <p>tel: {{ props.phone }}</p>
        </a>
    </div>
</template>
