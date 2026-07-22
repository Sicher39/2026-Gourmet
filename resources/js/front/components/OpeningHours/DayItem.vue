<script setup lang="ts">
const props = defineProps<{
  day?: string
  hours?: string
  footer?: boolean
}>()
</script>

<template>
  <div class="grid grid-cols-12 gap-x-4">
    <div class="col-span-4 md:col-span-3 lg:col-span-4">
      <p :class="footer ? 'text-sm text-white' : 'text-xl text-dark'">{{ props.day }}:</p>
    </div>
    <div class="col-span-8 md:col-span-8 lg:col-span-8">
      <p class=" text-left" :class="footer ? 'text-sm text-white' : 'text-xl text-dark'">{{ props.hours }}</p>
    </div>
  </div>
</template>
