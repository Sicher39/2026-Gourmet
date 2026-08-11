<script setup lang="ts">
import { computed } from 'vue'

type MenuVariant = {
    allergens?: string
    name: string
}

type MenuItem = {
    allergens?: string
    foodName: string
    price: number | string
    enabled: boolean
    menuVariants?: MenuVariant[]
}

const props = defineProps<{
    menuItems: MenuItem[]
}>()

const visibleMenuItems = computed(() => props.menuItems.filter((food) => food.enabled))
</script>

<template>
    <div
        class="mt-10 grid content-start grid-cols-12 px-2 py-10  mb-48 md:px-10"
    >
        <!-- day and date -->
        <div class="col-span-12 lg:col-span-4 w-full">
            <h3 class="font-head text-2xl font-bold uppercase text-primary">Snídaňové menu</h3>
        </div>

        <!-- breakfast meals -->
        <div class="col-span-12 mt-5 divide-y divide-accent lg:col-span-8 lg:mt-0">
            <div v-for="(food, i) in visibleMenuItems" :key="i" class="grid grid-cols-12 py-5">
                <div class="col-span-10 md:col-span-10">
                    <p class="text-primary">
                        {{ food.foodName }}
                        <span v-if="food.allergens">({{ food.allergens }})</span>
                    </p>
                    <ul v-if="food.menuVariants?.length" class="text-sm text-primary">
                        <li v-for="(variant, index) in food.menuVariants" :key="index">
                            {{ variant.name }}
                            <span v-if="variant.allergens"> – ({{ variant.allergens }})</span>
                        </li>
                    </ul>
                </div>

                <div class="col-span-2">
                    <p class="text-right text-primary">{{ food.price }}&nbsp;Kč</p>
                </div>
            </div>
        </div>
    </div>
</template>
