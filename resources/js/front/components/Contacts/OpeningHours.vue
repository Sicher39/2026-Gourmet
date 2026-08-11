<script setup lang="ts">

type OpenHour ={
    days: string
    hours: string
}

const props = defineProps<{
    section: string
    openingHours: OpenHour[]
}>()
</script>

<template>
    <div class="block my-5 px-5">
        <h4 class="text-lg md:text-xl font-bold text-dark mb-5">{{ section }}</h4>
        <div v-for="(item, i) in openingHours" :key="i" class="grid grid-cols-12">
            <div class="col-span-4 xl:col-span-2">
                <p class="uppercase text-sm">{{ item.days }}</p>
            </div>
            <div class="col-span-8 xl:col-span-8">
                <p class="text-sm">{{  item.hours }}</p>
            </div>
        </div>

    </div>
</template>