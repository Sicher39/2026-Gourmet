<script setup lang="ts">

import ButtonMain from "@/front/components/Buttons/ButtonMain.vue";
import {Link} from "@inertiajs/vue3";

const props = withDefaults(
    defineProps<{
      img?: string
      title?: string
      texts?: string[]
      lists?: string[]
      listTitle?: string
      left?: boolean
    }>(),
    {
        img: '',
        title: '',
        texts: () => [],
        lists: () => [],
        listTitle: '',
        left: true
    }
)
</script>

<template>
  <div class="grid grid-cols-2 gap-x-10 gap-y-20">
    <div :class="['block',
          left ? 'order-1' : 'order-2']">
      <img :src="`/img/bg/small/${props.img}.webp`"  class="w-full reveal" :alt="`ilustrační obrázek k sekci ${title}`">
    </div>
    <div :class="['block',
          left ? 'order-2' : 'order-1']">
      <h3 class="text-6xl text-accent reveal">{{ props.title }}</h3>
      <div class="block pl-32 reveal">
        <p v-for="(text, i) in texts" :key="i"  :class="{ 'mt-5': i === 0 }">
          {{ text }}
        </p>
        <p class="mt-10 reveal">
          {{ props.listTitle }}
        </p>
        <ul class="text-white list-disc list-outside pl-5 font-main py-2 reveal">
          <li v-for="(item, i) in lists" :key="i">{{ item }}</li>
        </ul>
      </div>
      <div class="block group w-[350px] mt-10 pl-16 reveal">
        <Link :href="route('front.contact')">
          <ButtonMain>nezávazná poptávka</ButtonMain>
        </Link>
      </div>
    </div>
  </div>
</template>