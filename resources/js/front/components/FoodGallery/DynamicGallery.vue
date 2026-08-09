<script setup lang="ts">
const props = withDefaults(
    defineProps<{
        images: string[]
        duration?: number
        mobileVisible?: number
        tabletVisible?: number
        desktopVisible?: number
    }>(),
    {
        duration: 120,
        mobileVisible: 1.5,
        tabletVisible: 2,
        desktopVisible: 3
    }
)

function getItemWidth(visibleImages: number, gap: string): string {
    const imageCount = Math.max(1, Math.floor(visibleImages))
    const gaps = Array.from({ length: imageCount - 1 }, () => gap).join(' - ')

    if (!gaps) {
        return '100vw'
    }

    return `calc((100vw - ${gaps}) / ${imageCount})`
}

const galleryStyle = {
    '--gallery-duration': `${props.duration}s`,
    '--gallery-mobile-item-width': getItemWidth(props.mobileVisible, '1.25rem'),
    '--gallery-tablet-item-width': getItemWidth(props.tabletVisible, '2.5rem'),
    '--gallery-desktop-item-width': getItemWidth(props.desktopVisible, '5rem')
}
</script>

<template>
    <div class="dynamic-gallery my-20 md:my-48 overflow-hidden" :style="galleryStyle">
        <div class="dynamic-gallery__track flex w-max gap-5 md:gap-10 lg:gap-20">
            <div
                v-for="(item, index) in images"
                :key="`${item}-${index}`"
                class="dynamic-gallery__item aspect-square shrink-0 bg-cover bg-center"
                :style="{
                    backgroundImage: `url('/img/actions/${item}.webp')`
                }"
            />
            <div
                v-for="(item, index) in images"
                :key="`${item}-${index}-duplicate`"
                aria-hidden="true"
                class="dynamic-gallery__item aspect-square shrink-0 bg-cover bg-center"
                :style="{
                    backgroundImage: `url('/img/actions/${item}.webp')`
                }"
            />
        </div>
    </div>
</template>

<style scoped>
.dynamic-gallery {
    --gallery-gap: 1.25rem;
    --gallery-half-gap: 0.625rem;
}

.dynamic-gallery__track {
    animation: dynamic-gallery-scroll var(--gallery-duration) linear infinite;
    will-change: transform;
}

@media (min-width: 768px) {
    .dynamic-gallery {
        --gallery-gap: 2.5rem;
        --gallery-half-gap: 1.25rem;
    }
}

@media (min-width: 1024px) {
    .dynamic-gallery {
        --gallery-gap: 5rem;
        --gallery-half-gap: 2.5rem;
    }
}

.dynamic-gallery__item {
    width: var(--gallery-mobile-item-width);
}

@media (min-width: 768px) {
    .dynamic-gallery__item {
        width: var(--gallery-tablet-item-width);
    }
}

@media (min-width: 1024px) {
    .dynamic-gallery__item {
        width: var(--gallery-desktop-item-width);
    }
}

@keyframes dynamic-gallery-scroll {
    to {
        transform: translateX(calc(-50% - var(--gallery-half-gap)));
    }
}

@media (prefers-reduced-motion: reduce) {
    .dynamic-gallery__track {
        animation-play-state: paused;
    }
}
</style>
