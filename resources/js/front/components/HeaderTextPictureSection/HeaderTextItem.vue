<script setup lang="ts">
import { Link } from '@inertiajs/vue3'

const props = withDefaults(
    defineProps<{
        image?: boolean
        header?: string
        link?: string
    }>(),
    {
        image: true,
        header: '',
        link: ''
    }
)
</script>

<template>
    <Link :href="route(link)">
        <div
            :class="[
                'relative w-full aspect-square overflow-hidden group cursor-pointer',
                image ? 'ponavka' : 'uVankovky'
            ]"
        >
            <div class="absolute -bottom-[2px] -left-[4px] lg:-left-[5px] xl:-left-[7px] 3xl:-left-[10px]">
                <h1 class="w-full group-hover:text-accent transition-colors duration-700 leading-[0.75] tracking-[-0.06em]">
                    {{ props.header }}
                </h1>
            </div>
        </div>
    </Link>
</template>
