<script setup lang="ts">
const props = withDefaults(
    defineProps<{
        image?: string
        name?: string
    }>(),
    {
        image: '',
        name: ''
    }
)
</script>

<template>
    <div class="relative w-full aspect-square overflow-hidden">
        <img
            :src="props.image"
            :alt="props.name"
            class="absolute inset-0 h-full w-full object-cover object-center"
        >
        <div class="absolute bottom-2 -left-2">
            <img src="/img/svg/knife.svg" class="w-8/12" alt="">
        </div>
        <div
            class="absolute bottom-[10px] left-[75px] md:bottom-[12px] md:left-[60px] xl:left-[75px]"
        >
            <p class="text-lg md:text-sm xl:text-lg">{{ props.name }}</p>
        </div>
    </div>
</template>
