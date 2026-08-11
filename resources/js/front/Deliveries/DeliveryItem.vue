<script setup lang="ts">
const props = withDefaults(
        defineProps<{
            img?: string
            alt?: string
            branch?: string
            link?: string
        }>(),
        {
            img: '',
            alt: '',
            branch: '',
            link: ''
        }
)
</script>

<template>
    <a :href="`${link}`" target="_blank">
        <div class="flex justify-start md:justify-start w-full">
            <div class="w-fit">
                <img :src="img" class="w-1/2" :alt="alt" />
                <p class="text-center md:text-left">{{ branch }}</p>
            </div>
        </div>
    </a>

</template>
