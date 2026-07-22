<script setup lang="ts">
import { computed } from 'vue'

const props = withDefaults(
    defineProps<{
        companyName?: string | null
        companyIdNumber?: string | null
        vatId?: string | null
        bankAccount?: string | null
    }>(),
    {
        companyName: null,
        companyIdNumber: null,
        vatId: null,
        bankAccount: null,
    }
)

const hasCompanyInfo = computed(
    (): boolean =>
        Boolean(props.companyName) ||
        Boolean(props.companyIdNumber) ||
        Boolean(props.vatId) ||
        Boolean(props.bankAccount)
)
</script>

<template>
    <div v-if="hasCompanyInfo" class="block w-fit py-10">
        <h5 class="text-xl font-bold text-accent-green">Společné údaje</h5>
        <div class="mt-1 w-10 border-b border-white" />
        <div class="block space-y-2">
            <p v-if="props.companyName">Název: {{ props.companyName }}</p>
            <p v-if="props.companyIdNumber">IČO: {{ props.companyIdNumber }}</p>
            <p v-if="props.vatId">DIČ: {{ props.vatId }}</p>
            <p v-if="props.bankAccount">bankovní účet: {{ props.bankAccount }}</p>
        </div>
    </div>
</template>
