<script setup lang="ts">
import H4header from "@/front/components/Sections/H4header.vue";

const props = withDefaults(
    defineProps<{
        num?: number | string
        unit?: string
    }>(),
    {
        num: 0,
        unit: 'D'
    }
)
</script>

<template>
    <div class="block bg-white py-2 px-4">
        <h4 class="font-head text-center text-3xl md:text-4xl lg:text-5xl">{{ props.num }}</h4>
        <div class="flex justify-center px-2">
            <div class="block w-8/2 border-b-2 border-dark"></div>
        </div>
        <h4 class="font-head text-center text-3xl md:text-4xl lg:text-5xl">{{ props.unit }}</h4>
    </div>
</template>
