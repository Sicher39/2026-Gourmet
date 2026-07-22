<script setup lang="ts">
import { computed } from 'vue'
import type { Component } from 'vue'
import IgIcon from '@/front/components/IconComponents/IgIcon.vue'
import LinkedInIcon from '@/front/components/IconComponents/LinkedInIcon.vue'
import FbIcon from '@/front/components/IconComponents/FbIcon.vue'

const iconComponent: Record<string, Component> = {
  facebook: FbIcon,
  linkedin: LinkedInIcon,
  instagram: IgIcon
}

const props = withDefaults(
  defineProps<{
    icon?: string
    link?: string
    url?: string
  }>(),
  {
    icon: '',
    link: '',
    url: ''
  }
)

const resolvedIcon = computed(() => (props.icon ?? '').trim().toLowerCase())

const resolvedUrl = computed(() => {
  const raw = (props.link || props.url || '').trim()
  if (!raw) return ''

  if (/^https?:\/\//i.test(raw)) {
    return raw
  }

  return `https://${raw}`
})
</script>

<template>
  <div v-if="iconComponent[resolvedIcon] && resolvedUrl" class="block">
    <div class="w-[44px] text-gray-600 hover:text-primary">
      <a :href="resolvedUrl" target="_blank" rel="noopener noreferrer">
        <component :is="iconComponent[resolvedIcon]" />
      </a>
    </div>
  </div>
</template>
