<script setup lang="ts">
const props = withDefaults(
    defineProps<{
        label?: string
    }>(),
    {
        label: ''
    }
)
</script>

<template>
    <div class="block bg-dark border border-accent-green px-1 lg:px-4 py-1">
        <p class="text-accent text-sm">
            {{ props.label }}
        </p>
    </div>
</template>
