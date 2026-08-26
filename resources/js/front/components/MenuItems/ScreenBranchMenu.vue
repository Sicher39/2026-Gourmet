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
    <main class="relative min-h-screen bg-white px-6 py-10 pb-20 2xl:px-16">
        <p
            v-if="todayMenu.isNonCookingDay"
            class="font-head text-2xl font-bold uppercase text-primary"
        >
            {{ todayMenu.nonCookingMessage ?? 'Dnes nevaříme' }}
        </p>

        <div v-else class="grid grid-cols-1 gap-10 lg:grid-cols-3 lg:gap-6">
            <section class="divide-y divide-accent">
                <div
                    v-for="soup in visibleSoups"
                    :key="`soup-${soup.soupIndex}`"
                    class="grid grid-cols-12 py-5"
                >
                    <div class="col-span-3">
                        <p class="text-primary">Polévka {{ soup.soupIndex }}</p>
                        <p class="text-sm font-light text-primary">*{{ soup.allergens }}</p>
                    </div>
                    <div class="col-span-2">
                        <p class="font-light text-primary">{{ soup.weight }}&nbsp;{{ soup.unit }}</p>
                    </div>
                    <div class="col-span-5">
                        <p class="text-primary">{{ soup.soupName }}</p>
                    </div>
                    <div class="col-span-2">
                        <p class="text-right text-primary">{{ soup.price }}&nbsp;Kč</p>
                    </div>
                </div>

                <div
                    v-for="item in visibleMenuItems"
                    :key="`menu-${item.menuIndex}`"
                    class="grid grid-cols-12 py-5"
                >
                    <div class="col-span-3">
                        <p class="text-primary">Menu {{ item.menuIndex }}</p>
                        <p class="text-sm font-light text-primary">*{{ item.allergens }}</p>
                    </div>
                    <div class="col-span-2">
                        <p class="font-light text-primary">{{ item.weight }}&nbsp;{{ item.unit }}</p>
                    </div>
                    <div class="col-span-5">
                        <p class="text-primary">{{ item.foodName }}</p>
                    </div>
                    <div class="col-span-2">
                        <p class="text-right text-primary">{{ item.price }}&nbsp;Kč</p>
                    </div>
                </div>
            </section>

            <section class="divide-y divide-accent">
                <div
                    v-for="item in visiblePizzaItems"
                    :key="`pizza-${item.menuIndex}`"
                    class="grid grid-cols-12 py-5"
                >
                    <div class="col-span-3">
                        <p class="text-primary">Pizza {{ item.menuIndex }}</p>
                        <p class="text-sm font-light text-primary">*{{ item.allergens }}</p>
                    </div>
                    <div class="col-span-2">
                        <p class="font-light text-primary">{{ item.weight }}&nbsp;{{ item.unit }}</p>
                    </div>
                    <div class="col-span-5">
                        <p class="text-primary">{{ item.pizzaName }}</p>
                    </div>
                    <div class="col-span-2">
                        <p class="text-right text-primary">{{ item.price }}&nbsp;Kč</p>
                    </div>
                </div>
            </section>

            <section class="divide-y divide-accent">
                <div
                    v-for="item in visibleGrillItems"
                    :key="`grill-${item.menuIndex}`"
                    class="grid grid-cols-12 py-5"
                >
                    <div class="col-span-3">
                        <p class="text-primary">Grill {{ item.menuIndex }}</p>
                        <p class="text-sm font-light text-primary">*{{ item.allergens }}</p>
                    </div>
                    <div class="col-span-2">
                        <p class="font-light text-primary">{{ item.weight }}&nbsp;{{ item.unit }}</p>
                    </div>
                    <div class="col-span-5">
                        <p class="text-primary">{{ item.grillName }}</p>
                    </div>
                    <div class="col-span-2">
                        <p class="text-right text-primary">{{ item.price }}&nbsp;Kč</p>
                    </div>
                </div>
            </section>
        </div>

        <p class="absolute right-6 bottom-6 text-sm text-primary 2xl:right-16">
            Alergeny na vyžádání u obsluhy
        </p>
    </main>
</template>
