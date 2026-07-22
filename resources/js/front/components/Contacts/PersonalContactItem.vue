<script setup lang="ts">
import { computed } from 'vue'

const props = withDefaults(
    defineProps<{
        name?: string
        tel?: string
        email?: string
        ic?: string
        address?: string
    }>(),
    {
        name: '',
        tel: '',
        email: '',
        ic: '',
        address: ''
    }
)

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
        <h5 v-if="props.name" class="text-xl font-bold text-accent-green">{{ props.name }}</h5>
        <div class="border-b border-white w-10 mt-1" />
        <a v-if="props.tel" :href="`tel:${props.tel}`">
            <p class="mt-2">tel: {{ props.tel }}</p>
        </a>
        <p v-if="props.email" v-html="obfuscatedEmailLink" />

        <div v-if="props.ic || props.address" class="border-b border-white w-10 mt-1" />
        <p v-if="props.ic" class="mt-2">IČ: {{ props.ic }}</p>
        <p v-if="props.address">adresa: {{ props.address }}</p>
    </div>
</template>
