<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import { onBeforeUnmount, onMounted, computed } from 'vue'
import BasicFoodMenu from '@/front/components/MenuItems/BasicFoodMenu.vue'
import type { BranchMenuDay, BranchMenuPayload } from '@/front/types/branch-menu'

const props = defineProps<{
    branchMenu?: BranchMenuPayload | null
}>()

const emptyToday: BranchMenuDay = {
    day: '',
    date: '',
    isNonCookingDay: true,
    nonCookingMessage: 'Dnes nevaříme',
    soupItems: [],
    menuItems: [],
    pizzaItems: [],
    grillItems: []
}

const todayMenu = computed<BranchMenuDay>(() => props.branchMenu?.today ?? emptyToday)

let menuRefreshInterval: ReturnType<typeof setInterval> | null = null

onMounted((): void => {
    menuRefreshInterval = setInterval((): void => {
        router.reload({
            only: ['branchMenu']
        })
    }, 60_000)
})

onBeforeUnmount((): void => {
    if (menuRefreshInterval !== null) {
        clearInterval(menuRefreshInterval)
        menuRefreshInterval = null
    }
})
</script>

<template>
    <main class="min-h-screen bg-white px-6 py-10 2xl:px-16">
        <BasicFoodMenu
            :day="todayMenu.day"
            :date="todayMenu.date"
            :is-non-cooking-day="todayMenu.isNonCookingDay"
            :non-cooking-message="todayMenu.nonCookingMessage"
            :soup-items="todayMenu.soupItems"
            :menu-items="todayMenu.menuItems"
            :pizza-items="todayMenu.pizzaItems"
            :grill-items="todayMenu.grillItems"
        />
    </main>
</template>
