<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount, computed } from 'vue'
import PhotoSwipeLightbox from 'photoswipe/lightbox'
import 'photoswipe/style.css'

type Certification = {
  certificateName: string
  images: { url: string; alt?: string }[]
}

const props = defineProps<{
  certifications: Certification[]
}>()

// 1) Sloučíme všechny obrázky do jednoho pole pro jeden swipe
const slides = computed(() =>
  (props.certifications ?? []).flatMap(c =>
    (c.images ?? []).map(img => ({
      url: img.url,
      alt: img.alt || ''
    }))
  )
)

const galleryRef = ref<HTMLElement | null>(null)
let lightbox: PhotoSwipeLightbox | null = null
const galleryId = `gallery-${Math.random().toString(36).slice(2, 8)}`

// Načtení rozměrů pro PhotoSwipe
const getImageSize = (src: string): Promise<{ width: number; height: number }> =>
  new Promise((resolve, reject) => {
    const img = new Image()
    img.onload = () => resolve({ width: img.naturalWidth, height: img.naturalHeight })
    img.onerror = reject
    img.src = src
  })

onMounted(async () => {
  const links = galleryRef.value?.querySelectorAll('a') ?? []
  for (const link of links) {
    const href = link.getAttribute('href')
    if (href && (!link.hasAttribute('data-pswp-width') || !link.hasAttribute('data-pswp-height'))) {
      try {
        const { width, height } = await getImageSize(href)
        link.setAttribute('data-pswp-width', String(width))
        link.setAttribute('data-pswp-height', String(height))
      } catch (err) {
        console.error(`Chyba při načítání obrázku: ${href}`, err)
      }
    }
  }

  // 2) PhotoSwipe bez caption pluginu → v lightboxu se nic nezobrazuje
  lightbox = new PhotoSwipeLightbox({
    gallery: `#${galleryId}`,
    children: 'a',
    pswpModule: () => import('photoswipe')
  })
  lightbox.init()
})

onBeforeUnmount(() => {
  lightbox?.destroy()
  lightbox = null
})
</script>

<template>
  <div
    :id="galleryId"
    ref="galleryRef"
    class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-4 xl:grid-cols-6 gap-4"
  >
    <a
      v-for="(s, i) in slides"
      :key="i"
      :href="s.url"
      class="block"
    >
      <img
        :src="s.url"
        :alt="s.alt"
        class="rounded w-full h-auto"
        loading="lazy"
      />
    </a>
  </div>
</template>
