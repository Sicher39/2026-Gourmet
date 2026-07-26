<script setup lang="ts">
import { Link } from '@inertiajs/vue3'

const props = defineProps<{
    link: string
    title: string
    active: boolean
}>()
</script>

<template>
    <Link
        :href="props.link"
        class="inline-flex w-fit items-center justify-end"
    >
        <div class="group block w-fit">
            <div class="flex w-fit items-start justify-end md:justify-start">
                <p
                    class=""
                    :class="[
                        'text-right text-xl lg:text-xl uppercase font-normal group-hover:pl-10 lg:group-hover:px-10 duration-700',
                        props.active
                            ? 'pl-10 lg:px-10'
                            : 'px-2'
                    ]"
                >
                    <span
                        :class="[
                            props.active
                                ? 'text-accent group-hover:text-accent'
                                : 'text-dark group-hover:text-accent'
                        ]"
                    >
                        {{ props.title }}
                    </span>
                </p>
            </div>
        </div>
    </Link>
</template>
