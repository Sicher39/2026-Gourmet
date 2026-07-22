<script setup lang="ts">
import GsapReveal from '@/front/components/gsap/GsapReveal.vue'

const props = withDefaults(
    defineProps<{
        headerOne?: string
        headerTwo?: string
        boldOne?: boolean
        boldTwo?: boolean
        note?: string
        even?: boolean
    }>(),
    {
        headerOne: 'headerOne',
        headerTwo: 'headerTwo',
        boldOne: false,
        boldTwo: false,
        note: 'notes',
        even: false
    }
)
</script>

<template>
    <GsapReveal :right="!even" class="w-full">
        <div :class="['flex w-full', even ? 'justify-start' : 'justify-end']">
            <div :class="['w-full lg:w-4/12', even ? 'pb-[200px]' : 'pb-0']">
                <div class="z-0 w-full bg-dark px-5 py-10">
                    <h4
                        :class="[
                            'font-main text-4xl text-accent uppercase',
                            boldOne ? 'font-bold' : 'font-thin'
                        ]"
                    >
                        {{ props.headerOne }}
                    </h4>
                    <h4
                        :class="[
                            'font-main text-4xl text-accent uppercase',
                            boldTwo ? 'font-bold' : 'font-thin'
                        ]"
                    >
                        {{ props.headerTwo }}
                    </h4>
                    <p class="text-lg text-white mt-20">
                        {{ props.note }}
                    </p>
                </div>
            </div>
        </div>
    </GsapReveal>
</template>
