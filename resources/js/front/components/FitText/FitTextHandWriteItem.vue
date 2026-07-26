<script setup lang="ts">
import { gsap } from 'gsap'
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue'

const props = withDefaults(
    defineProps<{
        text?: string
        minSize?: number
        maxSize?: number
        characterDuration?: number
    }>(),
    {
        text: '',
        minSize: 1,
        maxSize: Number.POSITIVE_INFINITY,
        characterDuration: 0.06
    }
)

const characters = computed(() => Array.from(props.text))
const containerElement = ref<HTMLElement | null>(null)
const headlineElement = ref<HTMLElement | null>(null)
const measurementElement = ref<HTMLElement | null>(null)
const characterElements = ref<HTMLElement[]>([])
const fontSize = ref(100)

let resizeObserver: ResizeObserver | null = null
let intersectionObserver: IntersectionObserver | null = null
let handwritingTimeline: gsap.core.Timeline | null = null
let hasPlayed = false

const resizeText = async (): Promise<void> => {
    await nextTick()

    const container = containerElement.value
    const headline = headlineElement.value

    if (!container || !headline) {
        return
    }

    await document.fonts?.ready

    const containerWidth = container.clientWidth

    headline.style.fontSize = '100px'

    const headlineWidth = headline.getBoundingClientRect().width

    if (containerWidth === 0 || headlineWidth === 0) {
        return
    }

    const calculatedSize = 100 * (containerWidth / headlineWidth)
    const calculatedFontSize = Math.min(props.maxSize, Math.max(props.minSize, calculatedSize))

    headline.style.fontSize = `${calculatedFontSize}px`
    fontSize.value = calculatedFontSize
}

const createHandwritingTimeline = (): void => {
    handwritingTimeline?.kill()

    handwritingTimeline = gsap.timeline({ paused: true })
    handwritingTimeline.set(characterElements.value, { autoAlpha: 0 })
    handwritingTimeline.to(characterElements.value, {
        autoAlpha: 1,
        duration: props.characterDuration,
        ease: 'none',
        stagger: props.characterDuration
    })
}

const playHandwritingOnce = (): void => {
    if (hasPlayed) {
        return
    }

    hasPlayed = true
    handwritingTimeline?.play()
    intersectionObserver?.disconnect()
}

watch(
    () => [props.text, props.minSize, props.maxSize],
    async () => {
        await resizeText()

        if (!hasPlayed) {
            createHandwritingTimeline()
        }
    }
)

onMounted(async () => {
    resizeObserver = new ResizeObserver(() => {
        void resizeText()
    })

    if (containerElement.value) {
        resizeObserver.observe(containerElement.value)
    }

    await resizeText()
    createHandwritingTimeline()

    intersectionObserver = new IntersectionObserver(
        (entries) => {
            if (entries.some((entry) => entry.isIntersecting)) {
                playHandwritingOnce()
            }
        },
        {
            rootMargin: '0px 0px -30% 0px',
            threshold: 0
        }
    )

    if (containerElement.value) {
        intersectionObserver.observe(containerElement.value)
    }
})

onBeforeUnmount(() => {
    resizeObserver?.disconnect()
    intersectionObserver?.disconnect()
    handwritingTimeline?.kill()
})
</script>

<template>
    <div class="flex w-full justify-center">
        <div
            ref="containerElement"
            class="z-30 flex w-10/12 -mt-[350px] justify-center"
        >
            <h3
                ref="headlineElement"
                :aria-label="props.text"
                class="inline-block whitespace-pre"
                :style="{ fontSize: `${fontSize}px` }"
            >
                <span
                    v-for="(character, index) in characters"
                    :key="`${character}-${index}`"
                    ref="characterElements"
                    aria-hidden="true"
                    class="whitespace-pre"
                    style="visibility: hidden; opacity: 0"
                >{{ character }}</span>
            </h3>

            <h3
                ref="measurementElement"
                aria-hidden="true"
                class="pointer-events-none absolute invisible inline-block whitespace-pre"
                style="font-size: 100px"
            >
                <span
                    v-for="(character, index) in characters"
                    :key="`${character}-${index}`"
                    class="whitespace-pre"
                >{{ character }}</span>
            </h3>
        </div>
    </div>
</template>
