<script setup lang="ts">
const props = withDefaults(
    defineProps<{
        amount?: string
        unit?: string
        title?: string
        description?: string
        allergens?: string
        price?: string | number
        second?: boolean
    }>(),
    {
        amount: '',
        unit: '',
        title: '',
        description: '',
        allergens: '',
        price: '',
        second: false,
    }
)
</script>

<template>
    <div :class="['grid grid-cols-12 mt-5 px-1',
    second ? 'bg-dark/10' : 'bg-white']">
        <div class="col-span-2 py-1">
            <p class="text-lg">
                {{ props.amount }}<span v-if="props.amount && props.unit"> </span>{{ props.unit }}
            </p>
            <div v-show="props.allergens !== '' " class="w-6/12 border-b border-dark"></div>
            <p
                    v-if="props.allergens"
                    class="text-sm"
            >
                ({{ props.allergens }})
            </p>
        </div>
        <div class="col-span-7 py-1">
            <p class="text-lg">
                {{ props.title }}
            </p>
            <p
                v-if="props.description"
                class="text-sm text-dark/70"
            >
                {{ props.description }}
            </p>
        </div>
        <div class="col-span-3 py-1">
            <p class="text-lg text-right">{{ props.price }} Kč</p>
        </div>
    </div>
</template>
