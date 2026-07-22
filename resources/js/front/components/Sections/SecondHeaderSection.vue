<script setup lang="ts">
import FullSection from "@/front/components/Sections/FullSection.vue";
import ButtonMain from "@/front/components/Buttons/ButtonMain.vue";
import {Link} from "@inertiajs/vue3";
import {nextTick, onBeforeUnmount, onMounted, ref} from "vue";
import {gsap} from "gsap";
import {ScrollTrigger} from "gsap/ScrollTrigger";

const props = withDefaults(
    defineProps<{
      img: string
      button?: string
      link?: string
    }>(),
    {
      img: '',
      button: '',
      link: ''
    }
)

const rootElement = ref<HTMLElement | null>(null)
let secondHeaderRevealContext: gsap.Context | null = null

onMounted(async (): Promise<void> => {
  await nextTick()

  if (!rootElement.value) {
    return
  }

  gsap.registerPlugin(ScrollTrigger)

  secondHeaderRevealContext = gsap.context(() => {
    gsap.set('.revealHeader', {y: 40, opacity: 0})
    gsap.set('.reveal-text', {
      clipPath: 'inset(0 100% 0 0)',
      filter: 'blur(16px)',
      opacity: 0,
      x: 0
    })
    gsap.set('.second-header-reveal-media', {y: 60, opacity: 0})

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
        opacity: 1,
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

    gsap.to('.second-header-reveal-media', {
      y: 0,
      opacity: 1,
      duration: 1.6,
      ease: 'power2.out',
      scrollTrigger: {
        trigger: '.second-header-reveal-media',
        start: 'top 80%',
        once: true
      }
    })
  }, rootElement.value)
})

onBeforeUnmount((): void => {
  secondHeaderRevealContext?.revert()
  secondHeaderRevealContext = null
})
</script>

<template>
  <FullSection>
    <div ref="rootElement" class="relative w-full mb-48">
      <div class="relative pt-32 z-20 w-full">
        <h1 class="text-white items-end lg:leading-[150px] text-5xl lg:text-8xl revealHeader  reveal-text">
          <slot name="header"/>
        </h1>
        <div class="block w-1/3 pl-20">
          <h2 class="text-white text-4xl revealHeader reveal-text mt-20">
            <slot name="title"/>
          </h2>
        </div>

        <div v-if="button !== '' && link !== ''" class="block w-[250px] group mt-10">
          <Link :href="route(`front.${props.link}`)">
            <ButtonMain><span class="uppercase font-main">{{ props.button }}</span></ButtonMain>
          </Link>
        </div>
      </div>

      <div class="absolute w-full top-[300px] z-10 revealHeader reveal">
        <div class="flex w-full justify-end">
          <img :src="`/img/bg/headers/${img}.webp`" class="w-[600px] aspect-auto mr-[300px]" alt="">
        </div>
      </div>
    </div>

  </FullSection>
</template>
