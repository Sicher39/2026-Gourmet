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
const visiblePizzaItems = computed(() => props.pizzaItems.filter((pizza) => pizza.enabled))
</script>

<template>
    <div class="grid grid-cols-1 md:grid-cols-2 md:gap-x-20 mt-10 py-10 px-5 md:px-10">
        <div class="block w-full">
            <h3 class="font-head uppercase text-primary font-bold text-2xl">Denní menu</h3>

            <!-- soups nad meals -->
            <div class="col-span-12 mt-5 divide-y divide-accent lg:col-span-8 lg:mt-0">
                <div
                    v-for="soup in visibleSoups"
                    :key="`soup-${soup.soupIndex}`"
                    class="grid grid-cols-12 py-5"
                >
                    <div class="col-span-3 md:col-span-2">
                        <p class="text-primary">Polévka {{ soup.soupIndex }}</p>

                        <p class="text-sm font-light text-primary">*{{ soup.allergens }}</p>
                    </div>

                    <div class="col-span-2 md:col-span-1">
                        <p class="font-light text-primary">{{ soup.weight }}&nbsp;l</p>
                    </div>

                    <div class="col-span-5 md:col-span-7">
                        <p class="text-primary">
                            {{ soup.soupName }}
                        </p>
                    </div>

                    <div class="col-span-2">
                        <p class="text-right text-primary">{{ soup.price }}&nbsp;Kč</p>
                    </div>
                </div>

                <div
                    v-for="food in visibleMenuItems"
                    :key="`menu-${food.menuIndex}`"
                    class="grid grid-cols-12 py-5"
                >
                    <div class="col-span-3 md:col-span-2">
                        <p class="text-primary">Menu {{ food.menuIndex }}</p>

                        <p class="text-sm font-light text-primary">*{{ food.allergens }}</p>
                    </div>

                    <div class="col-span-2 md:col-span-1">
                        <p class="font-light text-primary">{{ food.weight }}&nbsp;g</p>
                    </div>

                    <div class="col-span-5 md:col-span-7">
                        <p class="text-primary">
                            {{ food.foodName }}
                        </p>
                    </div>

                    <div class="col-span-2">
                        <p class="text-right text-primary">{{ food.price }}&nbsp;Kč</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="block w-full">
            <h3 class="font-head uppercase text-primary font-bold text-2xl">Pizza</h3>

            <!-- pizzas -->
            <div class="col-span-12 mt-5 divide-y divide-accent lg:col-span-8 lg:mt-0">

                <div
                        v-for="food in visiblePizzaItems"
                        :key="`menu-${food.menuIndex}`"
                        class="grid grid-cols-12 py-5"
                >
                    <div class="col-span-3 md:col-span-2">
                        <p class="text-primary">Menu {{ food.menuIndex }}</p>

                        <p class="text-sm font-light text-primary">*{{ food.allergens }}</p>
                    </div>

                    <div class="col-span-2 md:col-span-1">
                        <p class="font-light text-primary">{{ food.weight }}&nbsp;g</p>
                    </div>

                    <div class="col-span-5 md:col-span-7">
                        <p class="text-primary">
                            {{ food.pizzaName }}
                        </p>
                    </div>

                    <div class="col-span-2">
                        <p class="text-right text-primary">{{ food.price }}&nbsp;Kč</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
