<script setup lang="ts">
import FullSection from '@/front/components/Sections/FullSection.vue'

import { nextTick, onBeforeUnmount, onMounted, ref } from 'vue'
import { gsap } from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'

const rootElement = ref<HTMLElement | null>(null)
let headerRevealContext: gsap.Context | null = null

onMounted(async (): Promise<void> => {
    await nextTick()

    if (!rootElement.value) {
        return
    }

    gsap.registerPlugin(ScrollTrigger)

    headerRevealContext = gsap.context(() => {
        gsap.set('.revealHeader', { y: 40, opacity: 0 })
        gsap.set('.reveal-text', {
            clipPath: 'inset(0 100% 0 0)',
            filter: 'blur(16px)',
            x: 0
        })

        const headerRevealMediaElements = gsap.utils.toArray<HTMLElement>('.header-reveal-media')

        if (headerRevealMediaElements.length > 0) {
            gsap.set(headerRevealMediaElements, { y: 60, opacity: 0 })
        }

        ScrollTrigger.batch('.revealHeader', {
            start: 'top 90%',
            once: true,
            onEnter: (batch) =>
                gsap.to(batch, {
                    y: 0,
                    opacity: 1,
                    stagger: 0.15,
                    duration: 1.6,
                    ease: 'power2.out'
                })
        })

        gsap.utils.toArray<HTMLElement>('.reveal-text').forEach((element) => {
            gsap.to(element, {
                clipPath: 'inset(0 -10% 0 0)',
                filter: 'blur(0px)',
                x: 0,
                duration: 1.1,
                ease: 'power3.out',
                scrollTrigger: {
                    trigger: element,
                    start: 'top 90%',
                    once: true
                }
            })
        })

        headerRevealMediaElements.forEach((element) => {
            gsap.to(element, {
                y: 0,
                opacity: 1,
                duration: 1.6,
                ease: 'power2.out',
                scrollTrigger: {
                    trigger: element,
                    start: 'top 80%',
                    once: true
                }
            })
        })
    }, rootElement.value)
})

onBeforeUnmount((): void => {
    headerRevealContext?.revert()
    headerRevealContext = null
})

const props = withDefaults(
    defineProps<{
        headerOne?: string
        headerTwo?: string
        img?: string
    }>(),
    {
        headerOne: '',
        headerTwo: '',
        img: ''
    }
)
</script>

<template>
    <FullSection>
        <div ref="rootElement" class="relative w-full revealHeader">
            <div class="relative w-full">
                <div class="relative right-0 w-fit pt-27 h-screen md:h-fit lg:h-screen 3xl:max-h-[1080px] z-20">
                    <div class="block">
                        <h1 class="dark text-4xl lg:text-5xl 2xl:text-[110px] 3xl:text-[130px] reveal-text">
                            {{ props.headerOne }} <br />
                            {{ props.headerTwo }}
                        </h1>
                        <img
                            src="/img/logo/BK-u-sajmona.svg"
                            class="w-[150px] md:w-[250px] xl:w-[400px] mt-5 revealHeader"
                            alt="Logo hospůdky U Sajmona pod Hájkem"
                        />
                    </div>
                </div>
                <div class="absolute md:-right-[90px] top-[400px] md:top-[200px] w-full md:w-7/12 revealHeader">
                    <img
                        :src="`/img/bg/${props.img}.webp`"
                        class="w-full rounded-tl-4xl rounded-bl-4xl"
                        alt=""
                    />
                </div>
            </div>
        </div>
    </FullSection>
</template>
