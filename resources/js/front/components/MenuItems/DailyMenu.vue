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

const props = defineProps<{
    day: string
    date: string
    second?: boolean
    soupItems: SoupItem[]
    menuItems: MenuItem[]
}>()

const visibleSoups = computed(() => props.soupItems.filter((soup) => soup.enabled))

const visibleMenuItems = computed(() => props.menuItems.filter((food) => food.enabled))
</script>

<template>
    <div
        :class="[
            'weekly-menu-card-surface sticky grid content-start grid-cols-12 overflow-hidden rounded-xl px-2 py-10 shadow-lg md:px-10',
            second ? 'bg-card' : 'bg-headers']"
    >
        <!-- day and date stay fixed while an overflowing menu moves -->
        <div class="weekly-menu-card-header col-span-12 w-full lg:col-span-3 xl:col-span-4">
            <h3 class="font-head text-2xl font-bold uppercase text-primary">
                {{ props.day }} <br />
                {{ props.date }}
            </h3>
        </div>

        <!-- soups and meals -->
        <div class="weekly-menu-items-viewport col-span-12 mt-5 overflow-hidden lg:col-span-9 xl:col-span-8 lg:mt-0">
            <div class="weekly-menu-items divide-y divide-accent">
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
                        <p class="font-light text-primary">
                            {{ soup.weight }}&nbsp;l
                        </p>
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
                        <p class="font-light text-primary">
                            {{ food.weight }}&nbsp;g
                        </p>
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
    </div>
</template>
