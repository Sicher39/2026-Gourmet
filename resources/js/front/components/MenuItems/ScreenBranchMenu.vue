<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import { computed, onBeforeUnmount, onMounted } from 'vue'
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
const visibleSoups = computed(() => todayMenu.value.soupItems.filter((soup) => soup.enabled))
const visibleMenuItems = computed(() => todayMenu.value.menuItems.filter((item) => item.enabled))
const visiblePizzaItems = computed(() => todayMenu.value.pizzaItems.filter((item) => item.enabled))
const visibleGrillItems = computed(() => todayMenu.value.grillItems.filter((item) => item.enabled))

type ScreenMenuItem = {
    key: string
    label: string
    allergens: string
    weight: string
    unit: string
    name: string
    price: string
}

const screenMenuItems = computed<ScreenMenuItem[]>(() => [
    ...visibleSoups.value.map((item) => ({
        key: `soup-${item.soupIndex}`,
        label: `Polévka ${item.soupIndex}`,
        allergens: item.allergens,
        weight: item.weight,
        unit: item.unit,
        name: item.soupName,
        price: item.price
    })),
    ...visibleMenuItems.value.map((item) => ({
        key: `menu-${item.menuIndex}`,
        label: `Menu ${item.menuIndex}`,
        allergens: item.allergens,
        weight: item.weight,
        unit: item.unit,
        name: item.foodName,
        price: item.price
    })),
    ...visiblePizzaItems.value.map((item) => ({
        key: `pizza-${item.menuIndex}`,
        label: `Pizza ${item.menuIndex}`,
        allergens: item.allergens,
        weight: item.weight,
        unit: item.unit,
        name: item.pizzaName,
        price: item.price
    })),
    ...visibleGrillItems.value.map((item) => ({
        key: `grill-${item.menuIndex}`,
        label: `Grill ${item.menuIndex}`,
        allergens: item.allergens,
        weight: item.weight,
        unit: item.unit,
        name: item.grillName,
        price: item.price
    }))
])

const menuItemCount = computed(() => screenMenuItems.value.length)

const rowDensityClass = computed(() => {
    if (menuItemCount.value >= 13) {
        return 'py-0.5'
    }

    if (menuItemCount.value >= 9) {
        return 'py-1'
    }

    if (menuItemCount.value >= 6) {
        return 'py-2'
    }

    return 'py-4'
})

const itemTextClass = computed(() => {
    if (menuItemCount.value >= 13) {
        return 'text-xl leading-tight'
    }

    if (menuItemCount.value >= 9) {
        return 'text-2xl leading-tight'
    }

    if (menuItemCount.value >= 6) {
        return 'text-4xl leading-tight'
    }

    return 'text-6xl leading-tight'
})

const labelTextClass = computed(() => {
    if (menuItemCount.value >= 13) {
        return 'text-base leading-tight'
    }

    if (menuItemCount.value >= 9) {
        return 'text-lg leading-tight'
    }

    return 'text-2xl leading-tight'
})

const menuRowsStyle = computed(() => ({
    gridTemplateRows: `repeat(${Math.max(menuItemCount.value, 1)}, minmax(0, 1fr))`,
}))

const allergenTextClass = computed(() => {
    if (screenMenuItems.value.length >= 13) {
        return 'text-xs leading-tight'
    }

    return 'text-sm leading-tight'
})

const weightTextClass = computed(() => {
    if (screenMenuItems.value.length >= 13) {
        return 'text-sm leading-tight'
    }

    if (screenMenuItems.value.length >= 9) {
        return 'text-base leading-tight'
    }

    return 'text-lg leading-tight'
})

let menuRefreshInterval: ReturnType<typeof setInterval> | null = null

onMounted((): void => {
    menuRefreshInterval = setInterval((): void => {
        router.reload({
            only: ['branchMenu']
        })
    }, 30_000)
})

onBeforeUnmount((): void => {
    if (menuRefreshInterval !== null) {
        clearInterval(menuRefreshInterval)
        menuRefreshInterval = null
    }
})
</script>

<template>
    <main class="relative h-screen overflow-hidden bg-white px-6 py-4 pb-12 2xl:px-16">
        <p
            v-if="todayMenu.isNonCookingDay"
            class="font-head text-2xl font-bold uppercase text-primary"
        >
            {{ todayMenu.nonCookingMessage ?? 'Dnes nevaříme' }}
        </p>

        <section
            v-else
            class="grid h-[calc(100vh-4rem)] divide-y divide-accent"
            :style="menuRowsStyle"
        >
            <div
                v-for="item in screenMenuItems"
                :key="item.key"
                class="grid grid-cols-[8rem_4rem_minmax(0,1fr)_max-content] items-start gap-x-2"
                :class="rowDensityClass"
            >
                <div>
                    <p class="whitespace-nowrap text-primary" :class="labelTextClass">{{ item.label }}</p>
                    <p class="font-light text-primary" :class="allergenTextClass">*{{ item.allergens }}</p>
                </div>
                <div>
                    <p class="font-light text-primary" :class="weightTextClass">
                        {{ item.weight }}&nbsp;{{ item.unit }}
                    </p>
                </div>
                <div>
                    <p class="text-primary" :class="itemTextClass">{{ item.name }}</p>
                </div>
                <div class="justify-self-end">
                    <p class="whitespace-nowrap text-right text-primary" :class="itemTextClass">
                        {{ item.price }}&nbsp;Kč
                    </p>
                </div>
            </div>
        </section>

        <p class="absolute right-6 bottom-6 text-sm text-primary 2xl:right-16">
            Alergeny na vyžádání u obsluhy
        </p>
    </main>
</template>
