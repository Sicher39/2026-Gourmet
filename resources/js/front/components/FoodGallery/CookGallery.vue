<script setup lang="ts">
const props = withDefaults(
    defineProps<{
        image?: string
        name?: string
        isTeam?: boolean
    }>(),
    {
        image: '',
        name: '',
        isTeam: false
    }
)
</script>

<template>
    <div
        class="relative min-w-0 overflow-hidden"
        :class="props.isTeam
            ? 'aspect-video basis-full md:basis-0 md:flex-[16]'
            : 'aspect-square basis-full md:basis-0 md:flex-[9]'"
    >
        <img
            :src="props.image"
            :alt="props.name"
            class="absolute inset-0 h-full w-full object-cover object-center"
        >
        <div class="absolute bottom-2 -left-2">
            <img src="/img/svg/knife.svg" class="w-8/12" alt="">
        </div>
        <div
            class="absolute bottom-[10px] left-[75px] md:bottom-[15px] md:left-[60px] xl:left-[75px]"
        >
            <p class="text-sm ">{{ props.name }}</p>
        </div>
    </div>
</template>
