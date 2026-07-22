<script setup lang="ts">
import GsapReveal from '@/front/components/gsap/GsapReveal.vue'

const props = withDefaults(
    defineProps<{
        as?: string
        start?: string
        duration?: number
        scale?: number
        right?: boolean
    }>(),
    {
        as: 'div',
        start: 'top 90%',
        duration: 1.35,
        scale: 0,
        right: false,
    }
)
</script>

<template>
    <GsapReveal
        :as="props.as"
        :start="props.start"
        :duration="props.duration"
        :scale="props.scale"
        :right="props.right"
    >
        <slot />
    </GsapReveal>
</template>
