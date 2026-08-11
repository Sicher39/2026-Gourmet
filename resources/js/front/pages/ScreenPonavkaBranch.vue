<script setup lang="ts">
import ScreenBranchMenu from '@/front/components/MenuItems/ScreenBranchMenu.vue'
import type { BranchMenuPayload } from '@/front/types/branch-menu'

defineProps<{
    branchMenu?: BranchMenuPayload | null
}>()
</script>

<template>
    <ScreenBranchMenu :branch-menu="branchMenu" />
</template>
