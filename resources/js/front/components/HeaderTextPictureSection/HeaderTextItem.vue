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
    <Link>
        <div
            :class="[
                'relative w-full aspect-square overflow-hidden group cursor-pointer',
                image ? 'ponavka' : 'uVankovky'
            ]"
        >
            <div class="absolute -bottom-[0px] -left-[22px]">
                <h1 class="w-full group-hover:text-accent transition-colors duration-700">{{ props.header }}</h1>
            </div>
        </div>
    </Link>
</template>
