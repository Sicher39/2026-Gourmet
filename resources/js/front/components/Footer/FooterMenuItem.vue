<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps<{
  routeLink: string
  link: string
  title?: string
}>()

const page = usePage()

const normalizePath = (value: string) => {
  try {
    const url = new URL(value, window.location.origin)
    return url.pathname.replace(/\/+$/, '') || '/'
  } catch {
    return value.replace(/\/+$/, '') || '/'
  }
}

const isActive = computed(() => {
  return normalizePath(page.url) === normalizePath(props.link)
})
</script>

<template>
  <Link :href="props.link" class="inline-flex items-center py-1  underline-offset-4">
    <p class="text-sm" :class="isActive ? 'text-accent decoration-accent underline ' : 'text-primary hover:text-accent hover:underline hover:decoration-accent'">
      {{ props.title }}
    </p>
  </Link>
</template>
