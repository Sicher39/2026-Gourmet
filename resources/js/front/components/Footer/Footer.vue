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
            <div class="flex full justify-between w-full">
                <div class="flex">
                    <!-- year -& copyright-->
                    <p class="text-center font-main text-light text-white md:text-lg">
                        <span v-if="props.build === year"
                            >&copy; {{ year }} {{ props.company }} {{ copyright }}</span
                        >
                        <span v-else
                            >&copy; {{ props.build }}–{{ year }} {{ props.company }}
                            {{ copyright }}</span
                        >
                    </p>
                </div>
                <!-- Logo -->
                <div class="block w-[150px]">
                    <img src="/img/logo/gourmet-logo-wh.svg" class="w-full" alt="" />
                </div>
            </div>
        </FullSection>
    </footer>
</template>
