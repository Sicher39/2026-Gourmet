<script lang="ts" setup>
import { computed, onBeforeUnmount, onMounted, ref, type CSSProperties } from 'vue'
import { gsap } from 'gsap'

type ThreeColumnRevealOverlayProps = {
    color?: string
    colorClass?: string
    duration?: number
    stagger?: number
    ease?: string
    delay?: number
    zIndexClass?: string
    containerClass?: string
    panelClass?: string
}

const props = withDefaults(defineProps<ThreeColumnRevealOverlayProps>(), {
    color: '',
    colorClass: 'bg-dark-green',
    duration: 1.4,
    stagger: 0.18,
    ease: 'power3.out',
    delay: 0,
    zIndexClass: 'z-10',
    containerClass: '',
    panelClass: ''
})

const rootElement = ref<HTMLElement | null>(null)
const panelStyle = computed<CSSProperties>(() => {
    if (!props.color) {
        return {}
    }

    return {
        backgroundColor: props.color
    }
})
let revealContext: gsap.Context | null = null

onMounted((): void => {
    const root = rootElement.value

    if (!root) {
        return
    }

    revealContext = gsap.context(() => {
        const panels = gsap.utils.toArray<HTMLElement>('[data-reveal-panel]')

        if (!panels.length) {
            return
        }

        gsap.to(panels, {
            yPercent: (index: number): number => (index === 1 ? -100 : 100),
            duration: props.duration,
            ease: props.ease,
            stagger: props.stagger,
            delay: props.delay
        })
    }, root)
})

onBeforeUnmount((): void => {
    revealContext?.revert()
    revealContext = null
})
</script>

<template>
    <div
        ref="rootElement"
        aria-hidden="true"
        :class="['pointer-events-none absolute inset-0', props.zIndexClass, props.containerClass]"
    >
        <div class="grid h-full w-full grid-cols-3">
            <div
                data-reveal-panel
                :class="['h-full w-full', props.colorClass, props.panelClass]"
                :style="panelStyle"
            />
            <div
                data-reveal-panel
                :class="['h-full w-full', props.colorClass, props.panelClass]"
                :style="panelStyle"
            />
            <div
                data-reveal-panel
                :class="['h-full w-full', props.colorClass, props.panelClass]"
                :style="panelStyle"
            />
        </div>
    </div>
</template>
