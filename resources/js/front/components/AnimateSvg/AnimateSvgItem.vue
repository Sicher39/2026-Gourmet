<script setup lang="ts">
import { nextTick, onBeforeUnmount, onMounted, ref } from 'vue'
import { registerGsapPlugins, ScrollTrigger } from '@/front/utils/gsap'

const gsap = registerGsapPlugins()

function waitForLayout(): Promise<void> {
    return new Promise((resolve) => {
        requestAnimationFrame(() => {
            requestAnimationFrame(() => resolve())
        })
    })
}

const props = withDefaults(
    defineProps<{
        duration?: number
        stagger?: number
        delay?: number
        start?: string
        end?: string
        scrub?: number | boolean
        pin?: boolean
        pinSpacing?: boolean
        once?: boolean
        selector?: string
    }>(),
    {
        duration: 2,
        stagger: 0.15,
        delay: 0,
        start: 'top 90%',
        end: 'bottom 40%',
        scrub: 2,
        pin: false,
        pinSpacing: true,
        once: true,
        selector: 'path, line, polyline, polygon, rect, circle, ellipse'
    }
)

const root = ref<HTMLDivElement | null>(null)

let context: ReturnType<typeof gsap.context> | undefined

async function initializeAnimation(): Promise<void> {
    await nextTick()
    await document.fonts.ready
    await waitForLayout()

    const rootElement = root.value
    if (!rootElement) return

    const elements = Array.from(rootElement.querySelectorAll<SVGGeometryElement>(props.selector))
    const triggerElement = rootElement.querySelector<SVGSVGElement>('svg') ?? rootElement

    if (!elements.length) {
        console.warn(`AnimatedSvg: Nenalezeny elementy pro selektor "${props.selector}".`)
        return
    }

    // Spočítáme délky cest a schováme je (náhrada za placený DrawSVGPlugin).
    const pathLengths = elements.map((el) => el.getTotalLength())

    for (const [index, el] of elements.entries()) {
        const len = pathLengths[index]
        if (len === 0) {
            console.warn('AnimatedSvg: getTotalLength() vrátil 0 – SVG nemusí být vykreslené.')
        }
        const startPoint = el.getPointAtLength(0)
        const endPoint = el.getPointAtLength(len)
        const startsAtBottom = startPoint.y > endPoint.y

        el.style.strokeDasharray = `${len}`
        el.style.strokeDashoffset = startsAtBottom ? `-${len}` : `${len}`
    }

    // Zobrazíme SVG, teď už bez bliknutí.
    gsap.set(rootElement, { autoAlpha: 1 })

    context = gsap.context(() => {
        const timeline = gsap.timeline({
            scrollTrigger: {
                trigger: triggerElement,
                start: props.start,
                end: props.end,
                scrub: props.scrub,
                pin: props.pin,
                pinSpacing: props.pinSpacing,
                once: props.once
            }
        })

        const longestPathIndex = pathLengths.indexOf(Math.max(...pathLengths))
        const bodyElement = elements[longestPathIndex]
        const arrowHeadElements = elements.filter((_, index) => index !== longestPathIndex)

        timeline.to(bodyElement, {
            strokeDashoffset: 0,
            duration: props.duration,
            delay: props.delay,
            ease: 'none'
        }, 0)

        if (arrowHeadElements.length) {
            timeline.to(arrowHeadElements, {
                strokeDashoffset: 0,
                duration: props.duration * 0.1,
                delay: props.delay,
                stagger: props.stagger,
                ease: 'none'
            }, props.duration * 0.9)
        }
    }, rootElement)

    ScrollTrigger.refresh()
}

onMounted(() => {
    void initializeAnimation()
})

onBeforeUnmount(() => {
    context?.revert()
})
</script>

<template>
    <div ref="root" style="visibility: hidden">
        <slot />
    </div>
</template>
