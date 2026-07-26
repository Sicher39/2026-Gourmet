<script setup lang="ts">
import { nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue'

const props = withDefaults(
    defineProps<{
        text?: string
        minSize?: number
        maxSize?: number
    }>(),
    {
        text: undefined,
        minSize: 1,
        maxSize: Number.POSITIVE_INFINITY
    }
)

const containerElement = ref<HTMLElement | null>(null)
const measurementElement = ref<HTMLElement | null>(null)
const fontSize = ref(100)

let resizeObserver: ResizeObserver | null = null

const resizeText = async (): Promise<void> => {
    await nextTick()

    const container = containerElement.value
    const measurement = measurementElement.value

    if (!container || !measurement) {
        return
    }

    await document.fonts?.ready

    const containerWidth = container.clientWidth
    const measurementWidth = measurement.getBoundingClientRect().width

    if (containerWidth === 0 || measurementWidth === 0) {
        return
    }

    const measurementSize = 100
    const calculatedSize = measurementSize * (containerWidth / measurementWidth)

    fontSize.value = Math.min(props.maxSize, Math.max(props.minSize, calculatedSize))
}

watch(
    () => [props.text, props.minSize, props.maxSize],
    () => {
        void resizeText()
    }
)

onMounted(() => {
    resizeObserver = new ResizeObserver(() => {
        void resizeText()
    })

    if (containerElement.value) {
        resizeObserver.observe(containerElement.value)
    }

    void resizeText()
})

onBeforeUnmount(() => {
    resizeObserver?.disconnect()
})
</script>

<template>
    <div ref="containerElement" class="relative w-full overflow-hidden">
        <h2
            class="inline-block whitespace-nowrap"
            :style="{ fontSize: `${fontSize}px` }"
        >
            <slot>{{ props.text }}</slot>
        </h2>

        <h2
            ref="measurementElement"
            aria-hidden="true"
            class="pointer-events-none absolute invisible inline-block whitespace-nowrap"
            style="font-size: 100px"
        >
            <slot>{{ props.text }}</slot>
        </h2>
    </div>
</template>
