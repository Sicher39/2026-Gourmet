<script setup lang="ts">
import CookieConsent from '@/front/components/Cookies/CookieConsent.vue'
import Footer from '@/front/components/Footer/Footer.vue'
import Navigation from '@/front/components/Navigation/Navigation.vue'
import SeoHead from '@/front/components/Seo/SeoHead.vue'
</script>

<template>
    <SeoHead />

    <div class="w-full bg-dark-green">
        <header class="z-40 fixed w-full">
            <Navigation menu="branch"/>
        </header>
        <div class="relative min-h-screen overflow-x-clip bg-white">
            <div class="relative z-10 mx-auto max-w-[1920px]">
                <slot />
            </div>
        </div>
        <div class="">
            <Footer />
        </div>
    </div>
    <CookieConsent />
</template>
