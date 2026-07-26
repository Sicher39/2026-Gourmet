<script setup lang="ts">
defineProps<{
    images: string[]
}>()
</script>

<template>
    <div class="grid grid-cols-3 gap-20 px-10 my-48">
        <div
            v-for="item in images"
            :key="item"
            class="block w-full aspect-square bg-cover"
            :style="{
                backgroundImage: `url('/img/actions/${item}.webp')`
        }"
        />
    </div>
</template>
