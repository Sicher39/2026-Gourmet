<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, onMounted, ref } from 'vue'

type AnchorTabItem = {
    id: string
    label: string
}

const props = withDefaults(
    defineProps<{
        items?: AnchorTabItem[]
    }>(),
    {
        items: () => [] as AnchorTabItem[]
    }
)

const mobileTopOffset = 60
const desktopTopOffset = 60
const desktopBreakpoint = 768

const activeId = ref('')
const isPinned = ref(false)
const tabsHeight = ref(0)
const tabsLeft = ref(0)
const tabsWidth = ref(0)
const wrapperElement = ref<HTMLElement | null>(null)
const tabsElement = ref<HTMLElement | null>(null)
const contentElement = ref<HTMLElement | null>(null)

let observer: IntersectionObserver | null = null
let wrapperTop = 0

const currentTopOffset = (): number => {
    return window.innerWidth >= desktopBreakpoint ? desktopTopOffset : mobileTopOffset
}

const pinnedStyle = computed(() => {
    if (!isPinned.value) {
        return undefined
    }

    return {
        left: `${tabsLeft.value}px`,
        top: `${currentTopOffset()}px`,
        width: `${tabsWidth.value}px`
    }
})

const updateMeasurements = (): void => {
    const wrapper = wrapperElement.value
    const tabs = tabsElement.value
    const content = contentElement.value

    if (!wrapper || !tabs || !content) {
        return
    }

    const wrapperRectangle = wrapper.getBoundingClientRect()
    const contentRectangle = content.getBoundingClientRect()

    wrapperTop = wrapperRectangle.top + window.scrollY
    tabsHeight.value = tabs.offsetHeight
    tabsLeft.value = contentRectangle.left
    tabsWidth.value = contentRectangle.width
}

const updatePinnedState = (): void => {
    if (!isPinned.value) {
        updateMeasurements()
    }

    const shouldPin = window.scrollY >= wrapperTop - currentTopOffset()

    if (shouldPin === isPinned.value) {
        return
    }

    if (shouldPin) {
        updateMeasurements()
        isPinned.value = true
        return
    }

    isPinned.value = false
    void nextTick(updateMeasurements)
}

const handleResize = (): void => {
    isPinned.value = false
    void nextTick(updatePinnedState)
}

const scrollToSection = (id: string): void => {
    const section = document.getElementById(id)

    if (!section) {
        return
    }

    activeId.value = id
    section.scrollIntoView({
        behavior: 'smooth',
        block: 'start'
    })
}

onMounted(async () => {
    activeId.value = props.items[0]?.id ?? ''

    await nextTick()
    updatePinnedState()
    window.requestAnimationFrame(updatePinnedState)
    window.setTimeout(updatePinnedState, 300)

    window.addEventListener('load', updatePinnedState)
    window.addEventListener('scroll', updatePinnedState, { passive: true })
    window.addEventListener('resize', handleResize)

    observer = new IntersectionObserver(
        (entries) => {
            const visibleEntry = entries
                .filter((entry) => entry.isIntersecting)
                .sort((firstEntry, secondEntry) => secondEntry.intersectionRatio - firstEntry.intersectionRatio)[0]

            if (visibleEntry?.target.id) {
                activeId.value = visibleEntry.target.id
            }
        },
        {
            rootMargin: '-30% 0px -55% 0px',
            threshold: [0, 0.25, 0.5, 1]
        }
    )

    props.items.forEach((item) => {
        const section = document.getElementById(item.id)

        if (section) {
            observer?.observe(section)
        }
    })
})

onBeforeUnmount(() => {
    observer?.disconnect()
    window.removeEventListener('load', updatePinnedState)
    window.removeEventListener('scroll', updatePinnedState)
    window.removeEventListener('resize', handleResize)
})
</script>

<template>
    <div ref="wrapperElement" :style="isPinned ? { height: `${tabsHeight}px` } : undefined">
        <div
            ref="tabsElement"
            :class="isPinned ? 'fixed z-30' : 'relative z-30'"
            :style="pinnedStyle"
        >
            <div class="py-4">
                <div ref="contentElement" class="inline-flex flex-wrap gap-3">
                    <button
                        v-for="item in props.items"
                        :key="item.id"
                        type="button"
                        class="cursor-pointer border px-4 py-2 font-main text-sm uppercase tracking-wide transition-colors duration-200"
                        :class="
                            activeId === item.id
                                ? 'border-accent bg-accent text-dark-green'
                                : 'border-accent-green bg-dark text-accent hover:border-accent hover:text-white'
                        "
                        @click="scrollToSection(item.id)"
                    >
                        {{ item.label }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
