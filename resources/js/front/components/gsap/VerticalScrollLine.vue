<script lang="ts" setup>
import { nextTick, onBeforeUnmount, onMounted, ref } from 'vue'
import { gsap } from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'

type VerticalScrollLineProps = {
  targetSelector?: string
  start?: string
  end?: string
  scrub?: boolean | number
  observeResize?: boolean
  containerClass?: string
  lineClass?: string
}

const props = withDefaults(defineProps<VerticalScrollLineProps>(), {
  targetSelector: '',
  start: 'top bottom-=20%',
  end: 'bottom bottom-=10%',
  scrub: true,
  observeResize: true,
  containerClass: 'relative h-full w-px overflow-hidden',
  lineClass: 'absolute top-0 left-0 h-full w-px origin-top bg-accent'
})

const rootElement = ref<HTMLElement | null>(null)
const lineElement = ref<HTMLElement | null>(null)

let lineTween: gsap.core.Tween | null = null
let resizeObserver: ResizeObserver | null = null
let resizeTimeout: ReturnType<typeof setTimeout> | null = null

const resolveTriggerElement = (): HTMLElement | null => {
  if (props.targetSelector) {
    return document.querySelector<HTMLElement>(props.targetSelector)
  }

  return (
    rootElement.value?.closest<HTMLElement>('[data-vertical-scroll-line-container]')
    ?? rootElement.value?.parentElement
    ?? null
  )
}

const clearAnimation = (): void => {
  if (lineTween) {
    lineTween.scrollTrigger?.kill()
    lineTween.kill()
    lineTween = null
  }
}

const initializeAnimation = (): void => {
  const line = lineElement.value
  const triggerElement = resolveTriggerElement()

  if (!line || !triggerElement) {
    return
  }

  clearAnimation()

  gsap.set(line, {
    scaleY: 0,
    transformOrigin: 'top center'
  })

  lineTween = gsap.to(line, {
    scaleY: 1,
    ease: 'none',
    scrollTrigger: {
      trigger: triggerElement,
      start: props.start,
      end: props.end,
      scrub: props.scrub,
      invalidateOnRefresh: true
    }
  })
}

const scheduleRefresh = (): void => {
  if (resizeTimeout !== null) {
    clearTimeout(resizeTimeout)
  }

  resizeTimeout = setTimeout(() => {
    initializeAnimation()
    ScrollTrigger.refresh()
    resizeTimeout = null
  }, 120)
}

onMounted(async () => {
  await nextTick()

  gsap.registerPlugin(ScrollTrigger)
  initializeAnimation()

  if (!props.observeResize || typeof ResizeObserver === 'undefined') {
    return
  }

  const triggerElement = resolveTriggerElement()

  if (!triggerElement) {
    return
  }

  resizeObserver = new ResizeObserver(() => {
    scheduleRefresh()
  })

  resizeObserver.observe(triggerElement)
})

onBeforeUnmount(() => {
  clearAnimation()

  if (resizeTimeout !== null) {
    clearTimeout(resizeTimeout)
    resizeTimeout = null
  }

  resizeObserver?.disconnect()
  resizeObserver = null
})
</script>

<template>
  <div ref="rootElement" :class="props.containerClass" aria-hidden="true">
    <div ref="lineElement" :class="props.lineClass" />
  </div>
</template>
