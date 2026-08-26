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

const rowDensityClass = computed(() => {
    if (screenMenuItems.value.length >= 13) {
        return 'py-1.5'
    }

    if (screenMenuItems.value.length >= 9) {
        return 'py-2.5'
    }

    return 'py-4'
})

const itemTextClass = computed(() => {
    if (screenMenuItems.value.length >= 13) {
        return 'text-3xl leading-tight'
    }

    if (screenMenuItems.value.length >= 9) {
        return 'text-4xl leading-tight'
    }

    return 'text-5xl leading-tight'
})

const allergenTextClass = computed(() => {
    if (screenMenuItems.value.length >= 13) {
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
    <main class="relative min-h-screen bg-white px-6 py-6 pb-16 2xl:px-16">
        <p
            v-if="todayMenu.isNonCookingDay"
            class="font-head text-2xl font-bold uppercase text-primary"
        >
            {{ todayMenu.nonCookingMessage ?? 'Dnes nevaříme' }}
        </p>

        <section v-else class="divide-y divide-accent">
            <div
                v-for="item in screenMenuItems"
                :key="item.key"
                class="grid grid-cols-[minmax(11rem,18%)_5rem_minmax(0,1fr)_6rem]"
                :class="rowDensityClass"
            >
                <div>
                    <p class="text-primary" :class="itemTextClass">{{ item.label }}</p>
                    <p class="font-light text-primary" :class="allergenTextClass">*{{ item.allergens }}</p>
                </div>
                <div>
                    <p class="font-light text-primary" :class="itemTextClass">
                        {{ item.weight }}&nbsp;{{ item.unit }}
                    </p>
                </div>
                <div>
                    <p class="text-primary" :class="itemTextClass">{{ item.name }}</p>
                </div>
                <div>
                    <p class="text-right text-primary" :class="itemTextClass">{{ item.price }}&nbsp;Kč</p>
                </div>
            </div>
        </section>

        <p class="absolute right-6 bottom-6 text-sm text-primary 2xl:right-16">
            Alergeny na vyžádání u obsluhy
        </p>
    </main>
</template>
