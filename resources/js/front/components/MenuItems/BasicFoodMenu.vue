<script setup lang="ts">
import { computed } from 'vue'

type SoupItem = {
    soupIndex: number
    allergens: string
    weight: string
    unit?: string
    soupName: string
    price: number | string
    enabled: boolean
}

type MenuItem = {
    menuIndex: number
    allergens: string
    weight: string
    unit?: string
    foodName: string
    price: number | string
    enabled: boolean
}

type PizzaItem = {
    menuIndex: number
    allergens: string
    weight: string
    unit?: string
    pizzaName: string
    price: number | string
    enabled: boolean
}

type GrillItem = {
    menuIndex: number
    allergens: string
    weight: string
    unit?: string
    grillName: string
    price: number | string
    enabled: boolean
}

const props = defineProps<{
    day: string
    date: string
    second?: boolean
    isNonCookingDay?: boolean
    nonCookingMessage?: string | null
    pizzaItems?: PizzaItem[]
    grillItems?: GrillItem[]
    soupItems: SoupItem[]
    menuItems: MenuItem[]
}>()

const visibleSoups = computed(() => props.soupItems.filter((soup) => soup.enabled))
const visibleMenuItems = computed(() => props.menuItems.filter((food) => food.enabled))
const visiblePizzaItems = computed(() => props.pizzaItems?.filter((pizza) => pizza.enabled) ?? [])
const visibleGrillItems = computed(() => props.grillItems?.filter((grill) => grill.enabled) ?? [])
const hasSpecialtyMenu = computed(
    () => visiblePizzaItems.value.length > 0 || visibleGrillItems.value.length > 0
)
</script>

<template>
    <div
        :class="[
            'mt-10 grid grid-cols-1 py-10 px-5 2xl:px-10',
            hasSpecialtyMenu ? 'lg:grid-cols-2 lg:gap-x-5' : 'lg:grid-cols-12 lg:gap-x-10'
        ]"
    >
        <div :class="hasSpecialtyMenu ? 'w-full' : 'contents'">
            <div :class="hasSpecialtyMenu ? '' : 'lg:col-span-4'">
                <h3 class="font-head text-2xl font-bold uppercase text-primary">Denní menu</h3>
            </div>

            <div :class="hasSpecialtyMenu ? '' : 'lg:col-span-8'">
                <p
                v-if="props.isNonCookingDay"
                class="mt-5 font-head text-2xl font-bold uppercase text-primary lg:-mt-5"
            >
                {{ props.nonCookingMessage ?? 'Dnes nevaříme' }}
            </p>
            <div
                v-else
                class="divide-y divide-accent"
                :class="hasSpecialtyMenu ? 'mt-5' : 'lg:-mt-5'"
            >
                <div
                    v-for="soup in visibleSoups"
                    :key="`soup-${soup.soupIndex}`"
                    class="grid grid-cols-12 py-5"
                >
                    <div class="col-span-3 lg:col-span-3 xl:col-span-2">
                        <p class="text-primary">Polévka {{ soup.soupIndex }}</p>
                        <p class="text-sm font-light text-primary">*{{ soup.allergens }}</p>
                    </div>
                    <div class="col-span-2 lg:col-span-2 xl:col-span-2">
                        <p class="font-light text-primary">{{ soup.weight }}&nbsp;{{ soup.unit ?? 'l' }}</p>
                    </div>
                    <div class="col-span-5 lg:col-span-5 xl:col-span-7">
                        <p class="text-primary">{{ soup.soupName }}</p>
                    </div>
                    <div class="col-span-2 xl:col-span-1">
                        <p class="text-right text-primary">{{ soup.price }}&nbsp;Kč</p>
                    </div>
                </div>

                <div
                    v-for="food in visibleMenuItems"
                    :key="`menu-${food.menuIndex}`"
                    class="grid grid-cols-12 py-5"
                >
                    <div class="col-span-3 lg:col-span-3 xl:col-span-2">
                        <p class="text-primary">Menu {{ food.menuIndex }}</p>
                        <p class="text-sm font-light text-primary">*{{ food.allergens }}</p>
                    </div>
                    <div class="col-span-2 lg:col-span-2 xl:col-span-2">
                        <p class="font-light text-primary">{{ food.weight }}&nbsp;{{ food.unit ?? 'g' }}</p>
                    </div>
                    <div class="col-span-5 lg:col-span-5 xl:col-span-7">
                        <p class="text-primary">{{ food.foodName }}</p>
                    </div>
                    <div class="col-span-2 xl:col-span-1">
                        <p class="text-right text-primary">{{ food.price }}&nbsp;Kč</p>
                    </div>
                </div>
            </div>
        </div>
        </div>

        <div v-if="hasSpecialtyMenu" class="mt-10 w-full lg:col-start-2 lg:mt-0">
            <section v-if="visiblePizzaItems.length > 0">
                <h3 class="font-head text-2xl font-bold uppercase text-primary">Pizza</h3>
                <div class="mt-5 divide-y divide-accent">
                    <div
                        v-for="pizza in visiblePizzaItems"
                        :key="`pizza-${pizza.menuIndex}`"
                        class="grid grid-cols-12 py-5"
                    >
                        <div class="col-span-3 lg:col-span-3 xl:col-span-2">
                            <p class="text-primary">Pizza {{ pizza.menuIndex }}</p>
                            <p class="text-sm font-light text-primary">*{{ pizza.allergens }}</p>
                        </div>
                        <div class="col-span-2 lg:col-span-2 xl:col-span-2">
                            <p class="font-light text-primary">{{ pizza.weight }}&nbsp;{{ pizza.unit ?? 'g' }}</p>
                        </div>
                        <div class="col-span-5 lg:col-span-5 xl:col-span-7">
                            <p class="text-primary">{{ pizza.pizzaName }}</p>
                        </div>
                        <div class="col-span-2 xl:col-span-1">
                            <p class="text-right text-primary">{{ pizza.price }}&nbsp;Kč</p>
                        </div>
                    </div>
                </div>
            </section>

            <section :class="visiblePizzaItems.length > 0 ? 'mt-10' : ''" v-if="visibleGrillItems.length > 0">
                <h3 class="font-head text-2xl font-bold uppercase text-primary">Grill</h3>
                <div class="mt-5 divide-y divide-accent">
                    <div
                        v-for="grill in visibleGrillItems"
                        :key="`grill-${grill.menuIndex}`"
                        class="grid grid-cols-12 py-5"
                    >
                        <div class="col-span-3 lg:col-span-3 xl:col-span-2">
                            <p class="text-primary">Grill {{ grill.menuIndex }}</p>
                            <p class="text-sm font-light text-primary">*{{ grill.allergens }}</p>
                        </div>
                        <div class="col-span-2 lg:col-span-2 xl:col-span-2">
                            <p class="font-light text-primary">{{ grill.weight }}&nbsp;{{ grill.unit ?? 'g' }}</p>
                        </div>
                        <div class="col-span-5 lg:col-span-5 xl:col-span-7">
                            <p class="text-primary">{{ grill.grillName }}</p>
                        </div>
                        <div class="col-span-2 xl:col-span-1">
                            <p class="text-right text-primary">{{ grill.price }}&nbsp;Kč</p>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>
