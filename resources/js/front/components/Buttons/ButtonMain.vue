<script setup lang="ts">
defineProps<{
    href?: string
}>()
import ArrowRight from '@/front/components/AnimateSvg/SvgItems/ArrowRight.vue'
</script>

<template>
    <a
        :href="href"
        class="group inline-flex w-fit cursor-pointer flex-col items-start overflow-hidden px-3 py-1 text-dark hover:text-accent"
    >
        <span class="font-main font-thin text-2xl uppercase">
            <slot />
        </span>

        <ArrowRight
            class="w-10 -translate-x-full opacity-0 transition-all duration-700 group-hover:translate-x-0 group-hover:opacity-100"
        />
    </a>
</template>
