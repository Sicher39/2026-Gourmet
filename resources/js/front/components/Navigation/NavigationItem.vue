<script setup lang="ts">
import { nextTick } from 'vue'
import { Link, router } from '@inertiajs/vue3'

const props = defineProps<{
    link: string
    title: string
    active: boolean
}>()

const emit = defineEmits<{
    click: []
}>()

const waitForPageUnlock = async (): Promise<void> => {
    await nextTick()
    await new Promise<void>((resolve) => window.requestAnimationFrame(() => resolve()))
    await new Promise<void>((resolve) => window.requestAnimationFrame(() => resolve()))
}

const scrollToAnchor = async (anchor: string): Promise<void> => {
    await waitForPageUnlock()

    const element = document.getElementById(anchor)

    if (!element) {
        return
    }

    const headerHeight = document.querySelector('header')?.getBoundingClientRect().height ?? 0
    const top = element.getBoundingClientRect().top + window.scrollY - headerHeight

    window.scrollTo({
        top,
        left: 0,
        behavior: 'smooth'
    })
}

const handleClick = (event: MouseEvent): void => {
    const url = new URL(props.link, window.location.href)
    const anchor = url.hash.slice(1)

    if (!anchor || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
        emit('click')

        return
    }

    event.preventDefault()
    event.stopPropagation()
    emit('click')

    const currentUrl = new URL(window.location.href)
    const targetUrl = `${url.pathname}${url.search}${url.hash}`

    if (currentUrl.pathname === url.pathname && currentUrl.search === url.search) {
        window.history.pushState(null, document.title, targetUrl)
        void scrollToAnchor(anchor)

        return
    }

    router.visit(`${url.pathname}${url.search}`, {
        preserveScroll: true,
        onSuccess: () => {
            window.history.replaceState(null, document.title, targetUrl)
            void scrollToAnchor(anchor)
        }
    })
}
</script>

<template>
    <span class="contents" @click.capture="handleClick">
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
    </span>
</template>
