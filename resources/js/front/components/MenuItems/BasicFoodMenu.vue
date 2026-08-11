<script setup lang="ts">
import { computed } from 'vue'

type SoupItem = {
    soupIndex: number
    allergens: string
    weight: string
    soupName: string
    price: number
    enabled: boolean
}

type MenuItem = {
    menuIndex: number
    allergens: string
    weight: string
    foodName: string
    price: number
    enabled: boolean
}

type PizzaItem = {
    menuIndex: number
    allergens: string
    weight: string
    pizzaName: string
    price: number
    enabled: boolean
}

const props = defineProps<{
    day: string
    date: string
    second?: boolean
    pizzaItems?: PizzaItem[]
    soupItems: SoupItem[]
    menuItems: MenuItem[]
}>()

const visibleSoups = computed(() => props.soupItems.filter((soup) => soup.enabled))
const visibleMenuItems = computed(() => props.menuItems.filter((food) => food.enabled))
const visiblePizzaItems = computed(() => props.pizzaItems?.filter((pizza) => pizza.enabled) ?? [])
const hasPizza = computed(() => visiblePizzaItems.value.length > 0)
</script>

<template>
    <div
        :class="[
            'mt-10 grid grid-cols-1 py-10 px-5 2xl:px-10',
            hasPizza ? 'lg:grid-cols-2 lg:gap-x-5' : 'lg:grid-cols-12 lg:gap-x-10'
        ]"
    >
        <div :class="hasPizza ? 'w-full' : 'lg:col-span-4'">
            <h3 class="font-head text-2xl font-bold uppercase text-primary">Denní menu</h3>
        </div>

        <div :class="hasPizza ? 'w-full lg:col-start-1 lg:row-start-2' : 'lg:col-span-8'">
            <div class="divide-y divide-accent" :class="hasPizza ? 'mt-5' : 'lg:-mt-5'">
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
                        <p class="font-light text-primary">{{ soup.weight }}&nbsp;l</p>
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
                        <p class="font-light text-primary">{{ food.weight }}&nbsp;g</p>
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

        <template v-if="hasPizza">
            <div class="mt-10 w-full lg:col-start-2 lg:row-start-1 lg:mt-0">
                <h3 class="font-head text-2xl font-bold uppercase text-primary">Pizza</h3>
            </div>
            <div class="w-full lg:col-start-2 lg:row-start-2">
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
                            <p class="font-light text-primary">{{ pizza.weight }}&nbsp;g</p>
                        </div>
                        <div class="col-span-5 lg:col-span-5 xl:col-span-7">
                            <p class="text-primary">{{ pizza.pizzaName }}</p>
                        </div>
                        <div class="col-span-2 xl:col-span-1">
                            <p class="text-right text-primary">{{ pizza.price }}&nbsp;Kč</p>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>
