<script setup lang="ts">
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

const props = defineProps<{
    link: string
    title: string
}>()

const page = usePage()

const normalizePath = (value: string): string => {
    try {
        const url = new URL(value, window.location.origin)
        return url.pathname.replace(/\/+$/, '') || '/'
    } catch {
        return value.replace(/\/+$/, '') || '/'
    }
}

const isActive = computed(() => {
    return normalizePath(page.url) === normalizePath(props.link)
})
</script>

<template>
    <a :href="props.link" class="inline-flex w-full items-center justify-end">
        <div class="group block w-full">
            <!-- Keep each menu row equal width while anchoring the label to the right edge. -->
            <div class="flex w-full items-start justify-end md:justify-start px-2">
                <p class="text-right text-xl md:text-4xl uppercase font-thin duration-700 group-hover:pl-10">
                    <span :class="[isActive ? 'text-accent-green group-hover:text-accent-green' : 'text-white duration-700']">
                        {{ props.title }}
                    </span>
                </p>
            </div>
        </div>
    </a>
</template>
