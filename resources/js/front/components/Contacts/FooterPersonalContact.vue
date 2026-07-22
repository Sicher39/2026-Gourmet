<script setup lang="ts">
import { computed } from 'vue'

const props = withDefaults(
  defineProps<{
    position?: string
    phone?: string
    email?: string
  }>(),
  {
    position: '',
    phone: '',
    email: ''
  }
)

// Safely split email into user and domain parts
const emailParts = computed(() => {
  const raw = props.email ?? ''

  if (!raw || !raw.includes('@')) {
    return {
      user: raw,
      domain: ''
    }
  }

  const [user, ...rest] = raw.split('&#64;')

  return {
    user,
    domain: rest.join('@')
  }
})

// Text shown to the user, without plain '@'
const obfuscatedEmail = computed(() => {
  if (!emailParts.value.domain) {
    return emailParts.value.user
  }

  return `${emailParts.value.user}  ${emailParts.value.domain}`
})

// Build mailto only at runtime (no mailto in raw HTML markup)
const handleEmailClick = () => {
  if (!props.email) return

  window.location.href = `mai&#108;&#116;&#111;&#58;${props.email}`
}
</script>

<template>
  <div class="mt-5 w-full">
    <p class="text-primary text-lg">
      {{ props.position }}
    </p>

    <p v-if="props.phone" class="text-primary text-xl">
      tel:
      <a :href="`tel:${props.phone}`">
        {{ props.phone }}
      </a>
    </p>

    <p v-if="props.email" class="text-primary text-xl">
      e-mail:
      <button
        type="button"
        class="bg-transparent p-0 ml-1 cursor-pointer"
        @click="handleEmailClick"
      >
        {{ obfuscatedEmail }}
      </button>
    </p>
  </div>
</template>
