<script setup lang="ts">
import FullSection from '@/front/components/Sections/FullSection.vue'
import CornersBlockPhoto from '@/front/components/CornersBlock/CornersBlockPhoto.vue'
import ButtonMain from '@/front/components/Buttons/ButtonMain.vue'
import { Link } from '@inertiajs/vue3'
import H4header from '@/front/components/Sections/H4header.vue'

const props = withDefaults(
    defineProps<{
        header?: string
        note?: string
        imageOne?: string
        imageTwo?: string
        imageThree?: string
        button?: string
        link?: string
        url?: string
    }>(),
    {
        header: 'Header',
        note: 'Note',
        imageOne: '',
        imageTwo: '',
        imageThree: '',
        button: '',
        link: '',
        url: ''
    }
)

function imageSource(image: string): string {
    if (image.startsWith('/') || image.startsWith('http://') || image.startsWith('https://')) {
        return image
    }

    return `/img/bg/small/${image}.webp`
}
</script>

<template>
    <FullSection>
        <div class="block overflow-hidden lg:overflow-visible">
            <H4header>{{ props.header }}</H4header>
            <div class="grid grid-cols-12 w-full">
                <div class="col-span-12 lg:col-span-4 lg:pr-2 xl:pr-32">
                    <p class="mt-10 text-lg md:text-xl">
                        {{ props.note }}
                    </p>
                </div>
                <div class="col-span-12 lg:col-span-8 relative z-20 mt-[45px]">
                    <template v-if="props.imageOne">
                        <div class="relative w-full aspect-[23/14] lg:w-[690px]">
                            <img :src="imageSource(props.imageOne)" class="block h-full w-full object-cover" alt="" />
                            <CornersBlockPhoto />
                        </div>
                    </template>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2">
                <div class="relative z-30">
                    <div
                        class="relative mt-16 -left-[0px] -rotate-[16deg] w-full aspect-[23/14] lg:mt-0 lg:w-[690px] -top-[90px] lg:-top-[100px] lg:-right-[150px] lg:left-auto lg:-rotate-[20deg]"
                    >
                        <template v-if="props.imageTwo">
                            <img :src="imageSource(props.imageTwo)" class="block h-full w-full object-cover" alt="" />
                            <CornersBlockPhoto />
                        </template>
                    </div>
                </div>
                <div class="relative z-10">
                    <div
                        class="relative mt-10 -right-[0px] rotate-[16deg] w-full aspect-[23/14] lg:mt-0 lg:w-[690px] -top-[150px] lg:-top-[20px] lg:-right-[50px] lg:rotate-[20deg]"
                    >
                        <template v-if="props.imageThree">
                            <img :src="imageSource(props.imageThree)" class="block h-full w-full object-cover" alt="" />
                            <CornersBlockPhoto />
                        </template>
                    </div>
                </div>
            </div>
            <div
                v-if="props.button && (props.link || props.url)"
                class="flex justify-center w-full -mt-20 lg:mt-0 pb-10"
            >
                <Link v-if="props.link" :href="route(`${props.link}`)">
                    <ButtonMain>{{ props.button }}</ButtonMain>
                </Link>
                <a v-else-if="props.url" :href="props.url">
                    <ButtonMain>{{ props.button }}</ButtonMain>
                </a>
            </div>
        </div>
    </FullSection>
</template>
