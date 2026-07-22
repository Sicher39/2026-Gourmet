<script setup lang="ts">
import MainLayout from '@/front/layouts/MainLayout.vue'
import HeaderSection from '@/front/components/Sections/HeaderSection.vue'
import H3Header from '@/front/components/Sections/H3Header.vue'
import FlexSection from '@/front/components/Sections/FlexSection.vue'
import MenuItem from '@/front/components/MenuItems/MenuItem.vue'

interface MenuItemData {
    id: number
    amount: string
    unit: string
    title: string
    description: string
    allergens: string
    price: string | number
}

interface Section {
    id: number
    title: string
    items: MenuItemData[]
}

defineProps<{
    sections?: Section[]
}>()

defineOptions({
    layout: MainLayout,
    inheritAttrs: false
})
</script>
<template>
    <HeaderSection header-one="Co lze vypít," header-two="U Sajmona…" img="drinks-hand-drawing" />

    <FlexSection v-if="sections && sections.length">
        <div class="grid grid-cols-1 w-full mt-[200px]">
            <div v-for="section in sections" :key="section.id" class="block w-full mt-32">
                <H3Header>{{ section.title }}</H3Header>
                <div class="block w-[156px] border-b-[3px] border-dark mt-5" />

                <div class="block space-y-2">
                    <MenuItem
                        v-for="(item, idx) in section.items"
                        :key="item.id"
                        :amount="item.amount"
                        :unit="item.unit"
                        :title="item.title"
                        :description="item.description"
                        :allergens="item.allergens"
                        :price="item.price"
                        :second="idx % 2 === 1"
                    />
                </div>
            </div>
        </div>
    </FlexSection>

    <FlexSection v-else>
        <div class="block w-full mt-32 text-center">
            <p class="text-lg text-dark/60">Momentálně nemáme k dispozici žádný nápojový lístek.</p>
        </div>
    </FlexSection>

    <div class="block pb-[200px]"></div>
</template>
