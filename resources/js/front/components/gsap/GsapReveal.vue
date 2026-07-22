<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, onMounted, ref, useAttrs } from 'vue'
import { gsap } from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'

defineOptions({
    inheritAttrs: false,
})

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

const attrs = useAttrs()
const scopeElement = ref<HTMLElement | null>(null)
const animatedElement = ref<HTMLElement | null>(null)
let revealContext: gsap.Context | null = null

const animatedAttrs = computed(() => {
    const { class: className, ...restAttrs } = attrs

    void className

    return restAttrs
})

const animatedClasses = computed(() => [attrs.class, 'gsap-reveal'])

const getStartScrollPosition = (element: HTMLElement): number | string => {
    const startMatch = props.start.match(/^top\s+(bottom|\d+(?:\.\d+)?)%$/)

    if (!startMatch) {
        return props.start
    }

    const elementTop = element.getBoundingClientRect().top + window.scrollY
    const viewportOffset = startMatch[1] === 'bottom'
        ? window.innerHeight
        : window.innerHeight * (Number(startMatch[1]) / 100)

    return elementTop - viewportOffset
}

onMounted(async (): Promise<void> => {
    await nextTick()

    const scope = scopeElement.value
    const animated = animatedElement.value

    if (!scope || !animated) {
        return
    }

    gsap.registerPlugin(ScrollTrigger)

    revealContext = gsap.context(() => {
        const startScrollPosition = getStartScrollPosition(animated)

        gsap.set(animated, {
            scaleX: props.scale,
            scaleY: props.scale,
            opacity: 1,
            transformOrigin: props.right ? 'right bottom' : 'left bottom',
            willChange: 'transform',
        })

        gsap.to(animated, {
            scaleX: 1,
            scaleY: 1,
            duration: props.duration,
            ease: 'power2.out',
            scrollTrigger: {
                trigger: document.documentElement,
                start: startScrollPosition,
                once: true,
            },
            onComplete: () => {
                gsap.set(animated, { clearProps: 'willChange' })
            },
        })
    }, scope)
})

onBeforeUnmount((): void => {
    revealContext?.revert()
    revealContext = null
})
</script>

<template>
    <div ref="scopeElement" class="contents">
        <component
            :is="as"
            ref="animatedElement"
            v-bind="animatedAttrs"
            :class="animatedClasses"
        >
            <slot />
        </component>
    </div>
</template>
