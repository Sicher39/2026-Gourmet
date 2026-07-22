<script setup lang="ts">
import MainLayout from '@/front/layouts/MainLayout.vue'
import { computed, nextTick, onBeforeUnmount, onMounted, ref } from 'vue'
import PhotoSwipeLightbox from 'photoswipe/lightbox'
import 'photoswipe/style.css'
import type { EventGalleryData } from '@/front/types/event-gallery'
import ButtonMain from '@/front/components/Buttons/ButtonMain.vue'
import HeaderSection from "@/front/components/Sections/HeaderSection.vue";

defineOptions({
    layout: MainLayout,
    inheritAttrs: false
})

type EventGalleryPageData = EventGalleryData & {
    eventYear?: number | null
}

type GroupedGalleries = {
    year: string
    galleries: EventGalleryPageData[]
}

const props = defineProps<{
    eventGalleries?: EventGalleryPageData[]
    eventGalleriesNextPage?: number | null
}>()

const loadedGalleries = ref<EventGalleryPageData[]>(props.eventGalleries ?? [])
const nextPage = ref<number | null>(props.eventGalleriesNextPage ?? null)
const isLoadingMore = ref(false)

const groupedGalleries = computed<GroupedGalleries[]>(() => {
    const groups = new Map<string, EventGalleryPageData[]>()

    loadedGalleries.value.forEach((gallery) => {
        const eventYear =
            gallery.eventYear ?? (gallery.eventDate ? Number(gallery.eventDate.slice(0, 4)) : null)
        const year =
            eventYear !== null && !Number.isNaN(eventYear) ? eventYear.toString() : 'Bez data'

        if (!groups.has(year)) {
            groups.set(year, [])
        }

        groups.get(year)?.push(gallery)
    })

    return Array.from(groups.entries())
        .sort(([yearA], [yearB]) => {
            if (yearA === 'Bez data') {
                return 1
            }

            if (yearB === 'Bez data') {
                return -1
            }

            return Number(yearB) - Number(yearA)
        })
        .map(([year, galleries]) => ({
            year,
            galleries
        }))
})

// ── PhotoSwipe ────────────────────────────────────────────────────────

let lightboxes: PhotoSwipeLightbox[] = []

function initPhotoSwipe(): void {
    destroyPhotoSwipe()

    loadedGalleries.value.forEach((gallery) => {
        const el = document.getElementById('gallery-' + gallery.id)
        if (!el) {
            return
        }

        const lightbox = new PhotoSwipeLightbox({
            gallery: el,
            children: 'a',
            pswpModule: () => import('photoswipe')
        })
        lightbox.init()
        lightboxes.push(lightbox)
    })
}

function destroyPhotoSwipe(): void {
    lightboxes.forEach((lb) => lb.destroy())
    lightboxes = []
}

async function loadMoreGalleries(): Promise<void> {
    if (nextPage.value === null || isLoadingMore.value) {
        return
    }

    isLoadingMore.value = true

    try {
        const response = await window.axios.get(route('front.galleries.load-more'), {
            params: {
                page: nextPage.value
            }
        })

        loadedGalleries.value.push(...(response.data.galleries ?? []))
        nextPage.value = response.data.nextPage ?? null

        await nextTick()
        initPhotoSwipe()
    } finally {
        isLoadingMore.value = false
    }
}

onMounted(() => {
    initPhotoSwipe()
})

onBeforeUnmount(() => {
    destroyPhotoSwipe()
})
</script>

<template>

    <HeaderSection
            header-one="U Sajmona"
            header-two="to hlavně žije"
            img="action-hand-drawing"

    />
    <div class="bg-bgLight min-h-screen pb-32 mt-[100px]">
        <div class="max-w-7xl mx-auto px-5 lg:px-20 pt-32">
            <div v-if="groupedGalleries.length > 0" class="space-y-24">
                <section v-for="group in groupedGalleries" :key="group.year">
                    <h2 class="text-5xl lg:text-7xl mb-10">{{ group.year }}</h2>

                    <div class="columns-1 sm:columns-2 lg:columns-3 gap-5 lg:gap-8">
                        <article
                            v-for="gallery in group.galleries"
                            :id="'gallery-' + gallery.id"
                            :key="gallery.id"
                            class="mb-5 lg:mb-8 break-inside-avoid bg-white shadow-xl"
                        >
                            <a
                                v-if="gallery.photos[0]"
                                :href="gallery.photos[0].url"
                                :data-pswp-width="gallery.photos[0].width ?? 1200"
                                :data-pswp-height="gallery.photos[0].height ?? 800"
                                class="group relative block overflow-hidden"
                                :aria-label="`Otevřít galerii ${gallery.title}`"
                            >
                                <img
                                    :src="gallery.coverImageUrl ?? gallery.photos[0].url"
                                    :alt="gallery.photos[0].alt || gallery.title"
                                    class="w-full object-cover transition-transform duration-500 group-hover:scale-105"
                                    loading="lazy"
                                />

                                <div
                                    class="absolute inset-x-0 bottom-0 bg-linear-to-t from-dark/90 to-transparent px-4 pt-10 pb-2 opacity-100 transition-opacity duration-300 md:opacity-0 md:group-hover:opacity-100"
                                >
                                    <h3 class="text-xl text-white">{{ gallery.title }}</h3>
                                    <p v-if="gallery.dateLabel" class="text-sm text-white">
                                        {{ gallery.dateLabel }}
                                    </p>
                                </div>
                            </a>

                            <a
                                v-for="(photo, photoIndex) in gallery.photos.slice(1)"
                                :key="photoIndex"
                                :href="photo.url"
                                :data-pswp-width="photo.width ?? 1200"
                                :data-pswp-height="photo.height ?? 800"
                                class="hidden"
                            />
                        </article>
                    </div>
                </section>

                <div v-if="nextPage !== null" class="flex justify-center pt-4">
                    <button
                        type="button"
                        :disabled="isLoadingMore"
                        class="disabled:cursor-not-allowed disabled:opacity-60"
                        @click="loadMoreGalleries"
                    >
                        <ButtonMain>{{ isLoadingMore ? 'načítám…' : 'načíst další' }}</ButtonMain>
                    </button>
                </div>
            </div>

            <div v-else class="text-center py-20">
                <p class="text-2xl text-dark/60">Zatím žádné galerie.</p>
            </div>
        </div>
    </div>
</template>
