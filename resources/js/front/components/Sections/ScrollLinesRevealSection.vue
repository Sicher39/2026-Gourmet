<script setup lang="ts">
import FullSection from '@/front/components/Sections/FullSection.vue'
import { nextTick, onBeforeUnmount, onMounted, ref } from 'vue'
import { gsap } from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'

type ScrollLinesRevealSectionProps = {
  containerClass?: string
  headingClass?: string
}

const props = withDefaults(defineProps<ScrollLinesRevealSectionProps>(), {
  containerClass: 'w-full pb-32 bg-dark-green',
  headingClass: 'w-full text-center leading-12 xl:leading-20 3xl:leading-24 text-4xl md:text-5xl xl:text-6xl 3xl:text-7xl'
})

const headingElement = ref<HTMLElement | null>(null)

let linesTween: gsap.core.Tween | null = null
let resizeObserver: ResizeObserver | null = null
let resizeTimeout: ReturnType<typeof setTimeout> | null = null

let isUnmounted = false

type BreakSourceToken = { type: 'break'; className: string; style: string | null }

type SourceToken =
  | { type: 'text'; text: string }
  | BreakSourceToken

const sourceTokensCache = new WeakMap<HTMLElement, SourceToken[]>()

const getRenderedElementSize = (element: HTMLElement): { width: number; height: number } => ({
  width: Math.round(element.getBoundingClientRect().width),
  height: Math.round(element.getBoundingClientRect().height)
})

let renderedSizeCache: { width: number; height: number } | null = null

const hasRenderedSizeChanged = (nextSize: { width: number; height: number }): boolean => {
  if (!renderedSizeCache) {
    return true
  }

  return renderedSizeCache.width !== nextSize.width || renderedSizeCache.height !== nextSize.height
}

const updateRenderedSizeCache = (element: HTMLElement): void => {
  renderedSizeCache = getRenderedElementSize(element)
}

const clearAnimation = (): void => {
  if (linesTween) {
    linesTween.scrollTrigger?.kill()
    linesTween.kill()
    linesTween = null
  }
}

const breakableWhitespacePattern = /[\t\n\f\r ]+/g
const surroundingBreakableSpacesPattern = /^ +| +$/g
const breakableSpacePattern = / +/

const normalizeSegmentText = (text: string): string => text
  .replace(breakableWhitespacePattern, ' ')
  .replace(surroundingBreakableSpacesPattern, '')

const collectSourceTokens = (nodes: NodeListOf<ChildNode> | ChildNode[]): SourceToken[] => {
  const tokens: SourceToken[] = []

  Array.from(nodes).forEach((node) => {
    if (node.nodeType === Node.TEXT_NODE) {
      tokens.push({ type: 'text', text: node.textContent ?? '' })
      return
    }

    if (node.nodeType !== Node.ELEMENT_NODE) {
      return
    }

    const element = node as HTMLElement

    if (element.tagName === 'BR') {
      tokens.push({
        type: 'break',
        className: element.className,
        style: element.getAttribute('style')
      })
      return
    }

    tokens.push(...collectSourceTokens(element.childNodes))
  })

  return tokens
}

const getSourceTokens = (element: HTMLElement): SourceToken[] => {
  const cachedTokens = sourceTokensCache.get(element)

  if (cachedTokens) {
    return cachedTokens
  }

  const tokens = collectSourceTokens(element.childNodes)
  sourceTokensCache.set(element, tokens)

  return tokens
}

const isBreakVisible = (element: HTMLElement, token: BreakSourceToken): boolean => {
  if (!token.className && !token.style) {
    return true
  }

  const evaluator = document.createElement('span')
  evaluator.style.position = 'absolute'
  evaluator.style.width = '0'
  evaluator.style.height = '0'
  evaluator.style.overflow = 'hidden'
  evaluator.style.pointerEvents = 'none'
  evaluator.style.visibility = 'hidden'

  const breakElement = document.createElement('br')
  breakElement.className = token.className

  if (token.style) {
    breakElement.setAttribute('style', token.style)
  }

  evaluator.appendChild(breakElement)
  element.appendChild(evaluator)

  const isVisible = window.getComputedStyle(breakElement).display !== 'none'

  evaluator.remove()

  return isVisible
}

const extractSourceSegments = (element: HTMLElement): string[] => {
  const segments: string[] = []
  let currentSegment = ''

  getSourceTokens(element).forEach((token) => {
    if (token.type === 'text') {
      currentSegment += token.text
      return
    }

    if (!isBreakVisible(element, token)) {
      currentSegment += ' '
      return
    }

    const normalizedSegment = normalizeSegmentText(currentSegment)

    if (normalizedSegment.length > 0) {
      segments.push(normalizedSegment)
    }

    currentSegment = ''
  })

  const finalSegment = normalizeSegmentText(currentSegment)

  if (finalSegment.length > 0) {
    segments.push(finalSegment)
  }

  return segments
}

const buildScrollRevealLines = (element: HTMLElement): void => {
  const sourceSegments = extractSourceSegments(element)

  if (!sourceSegments.length) {
    return
  }

  element.textContent = ''

  const lines: string[] = []

  sourceSegments.forEach((segment) => {
    const measurableUnits = segment.split(breakableSpacePattern).filter((unit) => unit.length > 0)

    if (!measurableUnits.length) {
      return
    }

    const measureLayer = document.createElement('span')
    measureLayer.className = 'block'
    element.appendChild(measureLayer)

    measurableUnits.forEach((unit, index) => {
      const unitElement = document.createElement('span')
      unitElement.className = 'inline-block'
      unitElement.textContent = unit
      measureLayer.appendChild(unitElement)

      if (index < measurableUnits.length - 1) {
        measureLayer.appendChild(document.createTextNode(' '))
      }
    })

    const measuredUnits = Array.from(measureLayer.querySelectorAll<HTMLElement>('span'))
    let currentTop: number | null = null
    let currentLineUnits: string[] = []

    measuredUnits.forEach((unitElement) => {
      if (currentTop === null) {
        currentTop = unitElement.offsetTop
      }

      if (Math.abs(unitElement.offsetTop - currentTop) > 1) {
        lines.push(currentLineUnits.join(' '))
        currentLineUnits = []
        currentTop = unitElement.offsetTop
      }

      currentLineUnits.push(unitElement.textContent ?? '')
    })

    if (currentLineUnits.length) {
      lines.push(currentLineUnits.join(' '))
    }
  })

  element.textContent = ''

  lines.forEach((line) => {
    const lineElement = document.createElement('span')
    lineElement.className = 'relative block w-fit max-w-full mx-auto overflow-hidden'
    lineElement.style.lineHeight = 'inherit'

    const baseElement = document.createElement('span')
    baseElement.className = 'block whitespace-nowrap text-primary/10'
    baseElement.style.lineHeight = 'inherit'
    baseElement.textContent = line

    const overlayElement = document.createElement('span')
    overlayElement.className = 'scroll-line-overlay absolute top-0 left-0 block w-full overflow-hidden whitespace-nowrap text-white'
    overlayElement.style.lineHeight = 'inherit'
    overlayElement.setAttribute('aria-hidden', 'true')
    overlayElement.textContent = line

    lineElement.appendChild(baseElement)
    lineElement.appendChild(overlayElement)
    element.appendChild(lineElement)
  })
}

const initializeAnimation = (): void => {
  const element = headingElement.value

  if (!element) {
    return
  }

  clearAnimation()
  buildScrollRevealLines(element)

  const overlays = element.querySelectorAll<HTMLElement>('.scroll-line-overlay')

  gsap.set(overlays, { width: '0%' })

  linesTween = gsap.to(overlays, {
    width: '100%',
    ease: 'none',
    stagger: 0.12,
    scrollTrigger: {
      trigger: element,
      start: 'top 85%',
      end: 'top 20%',
      scrub: true
    }
  })

  updateRenderedSizeCache(element)
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

const handleWindowResize = (): void => {
  scheduleRefresh()
}

onMounted(async () => {
  await nextTick()

  gsap.registerPlugin(ScrollTrigger)
  initializeAnimation()
  window.addEventListener('resize', handleWindowResize)

  const element = headingElement.value

  if (element && typeof ResizeObserver !== 'undefined') {
    resizeObserver = new ResizeObserver((entries) => {
      entries.forEach((entry) => {
        const nextSize = {
          width: Math.round(entry.contentRect.width),
          height: Math.round(entry.contentRect.height)
        }

        if (!hasRenderedSizeChanged(nextSize)) {
          return
        }

        renderedSizeCache = nextSize
        scheduleRefresh()
      })
    })

    resizeObserver.observe(element)
  }

  const fontFaceSet = document.fonts

  if (fontFaceSet?.ready) {
    void fontFaceSet.ready.then(() => {
      if (!isUnmounted) {
        initializeAnimation()
      }
    })
  }
})

onBeforeUnmount(() => {
  isUnmounted = true

  clearAnimation()
  window.removeEventListener('resize', handleWindowResize)

  if (resizeTimeout !== null) {
    clearTimeout(resizeTimeout)
    resizeTimeout = null
  }

  resizeObserver?.disconnect()
  resizeObserver = null
})
</script>

<template>
  <FullSection>
    <div :class="props.containerClass">
      <h4 ref="headingElement" :class="props.headingClass">
        <slot />
      </h4>
    </div>
  </FullSection>
</template>
