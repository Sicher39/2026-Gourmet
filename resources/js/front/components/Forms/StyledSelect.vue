<script setup lang="ts">
import { computed, ref } from 'vue'

export type StyledSelectOptionValue = string | number | null

export type StyledSelectOption = {
    value: StyledSelectOptionValue
    label: string
    disabled?: boolean
}

const props = withDefaults(
    defineProps<{
        modelValue: StyledSelectOptionValue
        options: StyledSelectOption[]
        placeholder?: string
        disabled?: boolean
    }>(),
    {
        placeholder: 'Vyberte možnost',
        disabled: false
    }
)

const emit = defineEmits<{
    'update:modelValue': [value: StyledSelectOptionValue]
}>()

const isOpen = ref(false)
const selectedOption = computed(() => props.options.find((option) => option.value === props.modelValue) ?? null)
const selectedLabel = computed(() => selectedOption.value?.label ?? props.placeholder)

function toggle(): void {
    if (props.disabled) {
        return
    }

    isOpen.value = !isOpen.value
}

function selectOption(option: StyledSelectOption): void {
    if (option.disabled) {
        return
    }

    emit('update:modelValue', option.value)
    isOpen.value = false
}
</script>

<template>
    <div class="relative">
        <button
            type="button"
            class="form-input flex min-h-13 w-full items-center justify-between gap-3 text-left transition hover:border-accent focus:border-accent focus:outline-none disabled:cursor-not-allowed disabled:opacity-50"
            :disabled="disabled"
            @click="toggle"
        >
            <span :class="selectedOption ? 'text-light' : 'text-light/45'">{{ selectedLabel }}</span>
            <svg
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="1.5"
                stroke="currentColor"
                class="size-6 shrink-0 text-accent transition-transform"
                :class="isOpen ? 'rotate-180' : ''"
                aria-hidden="true"
            >
                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
            </svg>
        </button>

        <div
            v-if="isOpen && !disabled"
            class="absolute left-0 z-40 mt-3 max-h-72 w-full overflow-y-auto border border-accent/40 bg-dark/95 p-2 text-light shadow-2xl shadow-black/40 backdrop-blur"
        >
            <button
                v-for="option in options"
                :key="`${option.value}`"
                type="button"
                class="flex w-full items-center justify-between gap-3 px-3 py-3 text-left text-sm transition disabled:cursor-not-allowed disabled:opacity-35"
                :class="option.value === modelValue ? 'bg-accent text-dark' : 'text-light hover:bg-accent/15 hover:text-accent'"
                :disabled="option.disabled"
                @click="selectOption(option)"
            >
                <span>{{ option.label }}</span>
                <span v-if="option.value === modelValue" class="size-2 bg-current" aria-hidden="true"></span>
            </button>
        </div>
    </div>
</template>
