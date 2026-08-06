<script setup lang="ts">
const props = withDefaults(
    defineProps<{
        name?: string
        street?: string
        city?: string
        phone?: string
        email?: string
        companyNumber?: string
        dataBox?: string
        justice?: string
    }>(),
    {
        name: '',
        street: '',
        city: '',
        phone: '',
        email: '',
        companyNumber: '',
        dataBox: '',
        justice: ''
    }
)
</script>

<template>
    <div class="block my-5 px-5">
        <h4 class="text-xl font-bold text-dark">{{ name }}</h4>
        <p class="mt-5 text-sm">{{ street }}</p>
        <p class="text-sm">{{ city }}</p>

        <p class="text-sm mt-2">
            <a :href="`tel:${phone}`">{{ phone }}</a>
        </p>
        <p class="text-sm">
            <a :href="`mailto:${email}`">{{ email }}</a>
        </p>

        <template v-if="companyNumber !== ''">
            <p class="mt-2 text-sm">IČ:{{ companyNumber }}</p>
        </template>

        <template v-if="dataBox !== ''">
            <p class="text-sm">Datovka: {{ dataBox }}</p>
        </template>

        <template v-if="justice !== ''">
            <p class="text-sm">Spisová značka: {{ justice }}</p>
        </template>
    </div>
</template>
