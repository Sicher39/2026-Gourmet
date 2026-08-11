<script setup lang="ts">
type OpenHour = {
    days: string
    hours: string
}

const props = defineProps<{
    section?: string
    openingHours: OpenHour[]
}>()
</script>

<template>
    <div class="block my-5">
        <h4 v-if="section" class="text-lg md:text-xl font-bold text-dark mb-5">{{ section }}</h4>
        <div v-for="(item, i) in openingHours" :key="i" class="flex justify-start">
            <p class="uppercase font-bold text-dark pr-2">{{ item.days }}</p>
            <p class="font-bold text-dark">{{ item.hours }}</p>
        </div>
    </div>
</template>
