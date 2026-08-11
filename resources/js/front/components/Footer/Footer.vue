<script setup lang="ts">
import { computed } from 'vue'
import FullSection from '@/front/components/Sections/FullSection.vue'

interface Props {
    build?: number
    company?: string
}

const props = withDefaults(defineProps<Props>(), {
    build: 2015,
    company: ''
})

const copyright = '– Gourmet restaurant'

const year = computed(() => new Date().getFullYear())
</script>

<template>
    <footer class="bg-accent pt-10 pb-10">
        <FullSection>
            <div class="flex w-full flex-col gap-6 md:flex-row md:items-center md:justify-between">
                <div class="space-y-3">
                    <p class="font-main text-light text-white md:text-lg">
                        <span v-if="props.build === year"
                            >&copy; {{ year }} {{ props.company }} {{ copyright }}</span
                        >
                        <span v-else
                            >&copy; {{ props.build }}–{{ year }} {{ props.company }}
                            {{ copyright }}</span
                        >
                    </p>
                    <nav aria-label="Právní informace" class="flex flex-wrap gap-x-4 gap-y-2 text-sm text-white">
                        <a href="/ochrana-osobnich-udaju" class="underline underline-offset-4 hover:text-dark">
                            Ochrana osobních údajů
                        </a>
                        <a href="/zasady-cookies" class="underline underline-offset-4 hover:text-dark">
                            Zásady cookies
                        </a>
                    </nav>
                </div>
                <div class="block w-[150px]">
                    <img src="/img/logo/gourmet-logo-wh.svg" class="w-full" alt="Logo Gourmet Restaurant" />
                </div>
            </div>
        </FullSection>
    </footer>
</template>
