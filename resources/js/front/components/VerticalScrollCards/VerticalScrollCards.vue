<script setup lang="ts">
import ArrowLeft from '@/front/components/IconComponents/ArrowLeft.vue'
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import ButtonMain from '@/front/components/Buttons/ButtonMain.vue'
import { Link } from '@inertiajs/vue3'
import PhotoSwipeLightbox from 'photoswipe/lightbox'
import 'photoswipe/style.css'
import type { EventGalleryData } from '@/front/types/event-gallery'

const props = defineProps<{
    galleries?: EventGalleryData[]
}>()

const slider = ref<HTMLElement | null>(null)
const canScrollLeft = ref(false)
const canScrollRight = ref(false)
const isDragging = ref(false)
const sliderWidth = ref<string | null>(null)

const galleries = computed<EventGalleryData[]>(() => props.galleries ?? [])
const sliderCursorClass = computed(() => (isDragging.value ? 'cursor-grabbing' : 'cursor-grab'))

let lightboxes: PhotoSwipeLightbox[] = []
let animationFrameId: number | null = null
let cleanup: (() => void) | null = null
let pointerId: number | null = null
let startX = 0
let startScrollLeft = 0
let lastX = 0
let lastMoveTime = 0
let velocity = 0
let dragMoved = false
let clickShouldBeSuppressed = false
let currentScrollPosition = 0
let targetScrollPosition = 0

function initPhotoSwipe(): void {
    destroyPhotoSwipe()

    const galleryElements = slider.value?.querySelectorAll<HTMLElement>('[data-gallery-id]')

    galleryElements?.forEach((el) => {
        const lightbox = new PhotoSwipeLightbox({
            gallery: el,
            children: 'a',
            pswpModule: () => import('photoswipe'),
        })

        lightbox.init()
        lightboxes.push(lightbox)
    })
}

function destroyPhotoSwipe(): void {
    lightboxes.forEach((lightbox) => lightbox.destroy())
    lightboxes = []
}

function maxScrollLeft(element: HTMLElement): number {
    return Math.max(0, element.scrollWidth - element.clientWidth)
}

function clampScroll(element: HTMLElement, value: number): number {
    return Math.min(maxScrollLeft(element), Math.max(0, value))
}

function updateScrollButtons(): void {
    const element = slider.value

    if (!element) {
        canScrollLeft.value = false
        canScrollRight.value = false
        return
    }

    const threshold = 4

    canScrollLeft.value = element.scrollLeft > threshold
    canScrollRight.value = element.scrollLeft < maxScrollLeft(element) - threshold
}

function stopAnimation(): void {
    if (animationFrameId !== null) {
        cancelAnimationFrame(animationFrameId)
        animationFrameId = null
    }
}

function animateScroll(): void {
    const element = slider.value

    if (!element) {
        stopAnimation()
        return
    }

    currentScrollPosition += (targetScrollPosition - currentScrollPosition) * 0.12

    if (Math.abs(targetScrollPosition - currentScrollPosition) < 0.4) {
        currentScrollPosition = targetScrollPosition
    }

    element.scrollLeft = currentScrollPosition
    updateScrollButtons()

    if (Math.abs(targetScrollPosition - currentScrollPosition) >= 0.4) {
        animationFrameId = requestAnimationFrame(animateScroll)
    } else {
        stopAnimation()
    }
}

function startAnimation(): void {
    if (animationFrameId === null) {
        animationFrameId = requestAnimationFrame(animateScroll)
    }
}

function scrollByAmount(direction: number): void {
    const element = slider.value

    if (!element) {
        return
    }

    const amount = element.clientWidth * 0.8

    currentScrollPosition = element.scrollLeft
    targetScrollPosition = clampScroll(element, element.scrollLeft + direction * amount)

    startAnimation()
}

function onPointerDown(event: PointerEvent): void {
    const element = slider.value

    if (!element || (event.pointerType === 'mouse' && event.button !== 0)) {
        return
    }

    stopAnimation()

    pointerId = event.pointerId
    startX = event.clientX
    startScrollLeft = element.scrollLeft
    lastX = event.clientX
    lastMoveTime = performance.now()
    velocity = 0
    dragMoved = false
    clickShouldBeSuppressed = false
    isDragging.value = true
    currentScrollPosition = element.scrollLeft
    targetScrollPosition = element.scrollLeft
}

function onPointerMove(event: PointerEvent): void {
    const element = slider.value

    if (!element || !isDragging.value || pointerId !== event.pointerId) {
        return
    }

    const deltaX = event.clientX - startX

    if (Math.abs(deltaX) > 3) {
        dragMoved = true
        clickShouldBeSuppressed = true
        event.preventDefault()
    }

    const now = performance.now()
    const timeDelta = Math.max(1, now - lastMoveTime)
    velocity = (event.clientX - lastX) / timeDelta
    lastX = event.clientX
    lastMoveTime = now

    const nextScrollLeft = clampScroll(element, startScrollLeft - deltaX)

    element.scrollLeft = nextScrollLeft
    currentScrollPosition = nextScrollLeft
    targetScrollPosition = nextScrollLeft
    updateScrollButtons()
}

function endDrag(): void {
    const element = slider.value

    if (!element || !isDragging.value) {
        return
    }

    isDragging.value = false
    pointerId = null
    currentScrollPosition = element.scrollLeft

    if (dragMoved) {
        targetScrollPosition = clampScroll(element, element.scrollLeft - velocity * 420)
        startAnimation()
    } else {
        targetScrollPosition = currentScrollPosition
        updateScrollButtons()
    }
}

function suppressClickAfterDrag(event: MouseEvent): void {
    if (!clickShouldBeSuppressed) {
        return
    }

    event.stopImmediatePropagation()
    event.preventDefault()
    clickShouldBeSuppressed = false
}

function onScroll(): void {
    const element = slider.value

    if (!element || isDragging.value || animationFrameId !== null) {
        return
    }

    currentScrollPosition = element.scrollLeft
    targetScrollPosition = element.scrollLeft
    updateScrollButtons()
}

function updateSliderWidth(): void {
    const element = slider.value

    if (!element) {
        sliderWidth.value = null
        return
    }

    const leftOffset = element.getBoundingClientRect().left
    const width = Math.max(0, window.innerWidth - leftOffset)

    sliderWidth.value = `${width}px`
}

function onResize(): void {
    const element = slider.value

    if (!element) {
        return
    }

    updateSliderWidth()
    currentScrollPosition = clampScroll(element, element.scrollLeft)
    targetScrollPosition = clampScroll(element, targetScrollPosition)
    element.scrollLeft = currentScrollPosition
    updateScrollButtons()
}

onMounted(() => {
    const element = slider.value

    if (!element) {
        return
    }

    element.addEventListener('pointerdown', onPointerDown, true)
    document.addEventListener('pointermove', onPointerMove, true)
    document.addEventListener('pointerup', endDrag, true)
    document.addEventListener('pointercancel', endDrag, true)
    element.addEventListener('click', suppressClickAfterDrag, true)
    element.addEventListener('scroll', onScroll, { passive: true })
    window.addEventListener('resize', onResize)

    updateSliderWidth()
    initPhotoSwipe()
    updateScrollButtons()

    cleanup = () => {
        stopAnimation()
        destroyPhotoSwipe()
        element.removeEventListener('pointerdown', onPointerDown, true)
        document.removeEventListener('pointermove', onPointerMove, true)
        document.removeEventListener('pointerup', endDrag, true)
        document.removeEventListener('pointercancel', endDrag, true)
        element.removeEventListener('click', suppressClickAfterDrag, true)
        element.removeEventListener('scroll', onScroll)
        window.removeEventListener('resize', onResize)
    }
})

watch(() => props.galleries, async () => {
    await nextTick()
    updateSliderWidth()
    initPhotoSwipe()
    updateScrollButtons()
})

onBeforeUnmount(() => {
    cleanup?.()
})
</script>

<template>
    <section class="py-10 mb-32">
        <div class="relative">
            <div class="absolute right-4 -top-14 z-20 flex gap-3">
                <button
                    v-if="canScrollLeft"
                    type="button"
                    class="flex h-12 w-12 items-center justify-center border-b-1 border-dark text-dark transition hover:border-accent hover:text-accent"
                    @click="scrollByAmount(-1)"
                >
                    <ArrowLeft />
                </button>

                <button
                    v-if="canScrollRight"
                    type="button"
                    class="flex h-12 w-12 items-center justify-center border-b-1 border-dark text-dark transition hover:border-accent hover:text-accent"
                    @click="scrollByAmount(1)"
                >
                    <ArrowLeft class="rotate-180" />
                </button>
            </div>

            <div
                ref="slider"
                :style="{ width: sliderWidth ?? undefined }"
                :class="[
                    'overflow-x-auto overflow-y-hidden scrollbar-hide select-none touch-pan-x',
                    sliderCursorClass,
                ]"
            >
                <div class="flex gap-5 lg:gap-20 pl-5 lg:pl-20 pr-4 w-max">
                    <div
                        v-for="gallery in galleries"
                        :key="gallery.id"
                        class="w-[250px] lg:w-[350px] shrink-0 px-10 py-48 border border-white relative overflow-hidden group cursor-pointer"
                        :data-gallery-id="'gallery-' + gallery.id"
                    >
                        <div
                            class="absolute inset-0 bg-cover bg-center bg-no-repeat transition-transform duration-500 group-hover:scale-105"
                            :style="{ backgroundImage: gallery.coverImageUrl ? `url(${gallery.coverImageUrl})` : undefined }"
                        />

                        <a
                            v-if="gallery.photos[0]"
                            :href="gallery.photos[0].url"
                            draggable="false"
                            :data-pswp-width="gallery.photos[0].width ?? 1200"
                            :data-pswp-height="gallery.photos[0].height ?? 800"
                            class="absolute inset-0 z-10"
                            :aria-label="`Otevřít galerii ${gallery.title}`"
                        />
                        <a
                            v-for="(photo, photoIndex) in gallery.photos.slice(1)"
                            :key="photoIndex"
                            :href="photo.url"
                            draggable="false"
                            :data-pswp-width="photo.width ?? 1200"
                            :data-pswp-height="photo.height ?? 800"
                            class="hidden"
                        />

                        <div class="pointer-events-none absolute top-10 left-0 z-20 bg-white px-2 pb-5">
                            <h3 class="mb-2 text-xl font-semibold text-dark">
                                {{ gallery.title }}
                            </h3>
                            <p v-if="gallery.dateLabel" class="text-sm leading-2 text-dark/90">
                                {{ gallery.dateLabel }}
                            </p>
                        </div>
                    </div>

                    <div class="w-[250px] lg:w-[350px] shrink-0 bg-dark" data-slider-cta>
                        <div class="px-2 pb-5 mt-10">
                            <div class="relative -rotate-[15deg]">
                                <img
                                    src="/img/bg/sticky/samolepka-tuzemak2.webp"
                                    class="w-full"
                                    alt=""
                                    draggable="false"
                                />
                            </div>
                        </div>
                        <div class="relative top-20">
                            <div class="flex justify-center">
                                <Link :href="route('front.galleries')">
                                    <ButtonMain>
                                        <span class="text-white">zobrazit vše</span>
                                    </ButtonMain>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
