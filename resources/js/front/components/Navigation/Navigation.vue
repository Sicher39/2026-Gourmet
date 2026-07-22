<script setup lang="ts">
import { onMounted, onUnmounted, ref, watch } from 'vue'
import { Link } from '@inertiajs/vue3'
import NavigationItem from './NavigationItem.vue'
import { navLinks } from '@/front/components/Navigation/NavLinks'

const open = ref(false)
const scrolledFromTop = ref(false)
let previousBodyOverflow = ''

const lockPageScroll = (): void => {
    previousBodyOverflow = document.body.style.overflow
    document.body.style.overflow = 'hidden'
}

const unlockPageScroll = (): void => {
    document.body.style.overflow = previousBodyOverflow
}

const toggleMenu = (): void => {
    open.value = !open.value
}

const handleScroll = (): void => {
    scrolledFromTop.value = window.scrollY >= 50
}

const handleKeydown = (event: KeyboardEvent): void => {
    if (event.key === 'Escape' && open.value) {
        toggleMenu()
    }
}

watch(open, (isOpen) => {
    if (isOpen) {
        lockPageScroll()
        return
    }

    unlockPageScroll()
})

onMounted(() => {
    handleScroll()
    window.addEventListener('scroll', handleScroll)
    window.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
    unlockPageScroll()
    window.removeEventListener('scroll', handleScroll)
    window.removeEventListener('keydown', handleKeydown)
})
</script>

<template>
    <div class="flex w-full justify-center">
        <nav
            class="flex w-full max-w-480 items-center justify-center px-1 md:px-4 xl:px-0"
            :class="[scrolledFromTop ? 'bg-bgLight/50 backdrop-blur' : 'bg-bgLight backdrop-blur']"
        >
            <div class="flex w-full justify-between px-5 lg:px-10">
                <Link href="/" class="flex cursor-pointer justify-center px-2 md:px-0 md:pr-2">
                    <img
                        :src="`/img/logo/BK-u-sajmona-top.svg`"
                        class="z-50 my-1 transition-all duration-700 ease-out relative top-0"
                        :class="[
                            scrolledFromTop
                                ? 'w-[100px] smW:w-[100px] md:w-[120px]'
                                : 'md:w-[120px] md:w-[150px]'
                        ]"
                        alt="logo"
                        aria-label="logo"
                        width="150"
                        height="150"
                    />
                </Link>

                <!-- Menu toggle -->
                <div class="flex items-center space-x-2 text-white">
                    <button
                        type="button"
                        class="inline-flex h-10 items-center gap-2 border border-dark  bg-bgLight/0 px-2 text-dark hover:text-white hover:bg-accent md:px-3"
                        aria-label="Toggle menu"
                        aria-controls="mobile-menu"
                        :aria-expanded="open"
                        @click="toggleMenu"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                            aria-hidden="true"
                            class="h-5 w-5 shrink-0"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25H12"
                            />
                        </svg>
                        <span class="hidden text-sm font-bold uppercase tracking-wide md:inline"
                            >menu</span
                        >
                    </button>
                </div>
            </div>
        </nav>

        <!-- Fixed overlay layer keeps the opened menu independent from the header layout. -->
        <div
            id="mobile-menu"
            :aria-hidden="!open"
            class="fixed inset-0 z-50 flex justify-end"
            :class="open ? 'pointer-events-auto' : 'pointer-events-none'"
        >
            <!-- Clickable backdrop closes the menu without visually occupying the whole menu width. -->
            <div
                class="absolute inset-0 bg-accent transition-opacity duration-300"
                :class="open ? 'opacity-100' : 'opacity-0'"
                @click="toggleMenu"
            />

            <!-- Right panel width: 8/12 on mobile and about 2/3 on wider screens. -->
            <aside
                class="relative z-50 flex h-screen w-12/12 flex-col overflow-y-auto bg-acent pt-[10px] transition-transform duration-300 smW:w-1/3 smW:pt-[10px]"
                :class="open ? 'translate-x-0' : 'translate-x-full'"
                role="dialog"
                aria-modal="true"
                aria-label="Mobile menu"
            >
                <!-- Dedicated close button keeps the close icon visible even though the header button remains a hamburger. -->
                <button
                    type="button"
                    class="mb-6 mr-5 inline-flex h-11 w-11 items-center justify-center self-end border border-white bg-dark text-white group cursor-pointer"
                    aria-label="Close menu"
                    @click="toggleMenu"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                        aria-hidden="true"
                        class="h-7 w-7 group-hover:rotate-360 group-hover:duration-700"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>

                <!-- Every navigation item fills the panel width; the item component aligns labels to the right. -->
                <div class="block space-y-4 pl-10">
                    <NavigationItem
                        v-for="(link, index) in navLinks"
                        :key="index"
                        :link="link.link"
                        :title="link.title"
                        @click="toggleMenu"
                    />
                </div>
            </aside>
        </div>
    </div>
</template>
