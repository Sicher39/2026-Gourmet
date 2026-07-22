<script lang="ts" setup>
import MainLayout from '@/front/layouts/MainLayout.vue'
import FullSection from '@/front/components/Sections/FullSection.vue'
import { computed, onMounted, onUnmounted, ref } from 'vue'
import IndexPhotoBlock from '@/front/components/Sections/IndexPhotoBlock.vue'
import CounterItem from '@/front/components/TimerSection/CounterItem.vue'
import VerticalScrollCards from '@/front/components/VerticalScrollCards/VerticalScrollCards.vue'
import type { EventGalleryData } from '@/front/types/event-gallery'
import ButtonMain from '@/front/components/Buttons/ButtonMain.vue'
import { Link } from '@inertiajs/vue3'
import HeaderSection from '@/front/components/Sections/HeaderSection.vue'
import H3Header from '@/front/components/Sections/H3Header.vue'
import H4header from '@/front/components/Sections/H4header.vue'

defineOptions({
    layout: MainLayout,
    inheritAttrs: false
})

type RestaurantBirthday = {
    month: number
    day: number
    time: string
}

type PhotoSection = {
    header: string
    note: string
    imageOne: string
    imageTwo: string
    imageThree: string
    button: string
    link?: string
    url?: string
}

type PhotoSectionHandle = 'food' | 'drinks' | 'events' | 'weddings'

const props = defineProps<{
    restaurantBirthday?: RestaurantBirthday | null
    eventGalleries?: EventGalleryData[]
    photoSections?: Partial<Record<PhotoSectionHandle, Partial<PhotoSection>>>
}>()

const sections = computed(() => props.photoSections ?? {})

// ── Birthday countdown ────────────────────────────────────────────────

const hasBirthday = computed<boolean>(() => {
    return (
        props.restaurantBirthday?.month != null &&
        props.restaurantBirthday?.day != null &&
        props.restaurantBirthday?.time != null
    )
})

const displayNum1 = ref<number>(0)
const displayNum2 = ref<number>(0)
const displayNum3 = ref<number>(0)
const displayUnit1 = ref<string>('d')
const displayUnit2 = ref<string>('h')
const displayUnit3 = ref<string>('s')
const isToday = ref<boolean>(false)
const isDetailedCountdown = ref<boolean>(false)

function parseTime(time: string): { hours: number; minutes: number } {
    const [hours = '0', minutes = '0'] = time.split(':')

    return {
        hours: Number(hours),
        minutes: Number(minutes)
    }
}

function getNextTarget(birthday: RestaurantBirthday): Date {
    const now = new Date()
    const { hours, minutes } = parseTime(birthday.time)

    const target = new Date(
        now.getFullYear(),
        birthday.month - 1,
        birthday.day,
        hours,
        minutes,
        0,
        0
    )

    const birthdayEnd = new Date(target)
    birthdayEnd.setDate(birthdayEnd.getDate() + 1)
    birthdayEnd.setHours(0, 0, 0, 0)

    if (now.getTime() >= birthdayEnd.getTime()) {
        target.setFullYear(target.getFullYear() + 1)
    }

    return target
}

function updateCountdown(): void {
    if (!hasBirthday.value || props.restaurantBirthday == null) return

    const target = getNextTarget(props.restaurantBirthday)
    const now = new Date()

    const birthdayEnd = new Date(target)
    birthdayEnd.setDate(birthdayEnd.getDate() + 1)
    birthdayEnd.setHours(0, 0, 0, 0)

    if (now.getTime() >= target.getTime() && now.getTime() < birthdayEnd.getTime()) {
        isToday.value = true
        return
    }

    isToday.value = false
    const diffMs = target.getTime() - now.getTime()

    if (diffMs <= 0) {
        target.setFullYear(target.getFullYear() + 1)
        updateCountdown()
        return
    }

    const totalHours = diffMs / (1000 * 60 * 60)
    const totalDays = diffMs / (1000 * 60 * 60 * 24)

    if (totalDays > 2) {
        const d = Math.floor(totalDays)
        const h = Math.floor(totalHours % 24)
        displayNum1.value = d
        displayNum2.value = h
        displayUnit1.value = 'd'
        displayUnit2.value = 'h'
        isDetailedCountdown.value = false
    } else {
        const h = Math.floor(totalHours)
        const m = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60))
        const s = Math.floor((diffMs % (1000 * 60)) / 1000)
        displayNum1.value = h
        displayNum2.value = m
        displayNum3.value = s
        displayUnit1.value = 'h'
        displayUnit2.value = 'm'
        displayUnit3.value = 's'
        isDetailedCountdown.value = true
    }
}

let interval: ReturnType<typeof setInterval> | null = null

onMounted(() => {
    updateCountdown()
    interval = setInterval(updateCountdown, 1_000)
})

onUnmounted(() => {
    if (interval !== null) {
        clearInterval(interval)
        interval = null
    }
})
</script>

<template>
    <div>
        <div class="bg-bgLight pb-10">
            <HeaderSection
                header-one="Hledali jsme štěstí,"
                header-two="našli jsme hospodu…"
                img="burger-hand-drawing"
            />

            <FullSection>
                <div class="block py-10 md:py-32 lg:-mt-20">
                    <h2 class="text-4xl md:text-7xl">Hospoda, kde to má charakter</h2>
                    <h3 class="font-main text-2xl md:text-2xl lg::text-4xl mt-5">
                        Poctivé pivo. Dobré jídlo. Atmosféra, kvůli které se vrátíte.
                    </h3>
                    <p class="text-lg md:text-xl lg:text-2xl mt-2">
                        Žádná strojenost. Žádné hraní na něco. Nejsme restaurace, kde je ticho a
                        prázdné stoly. <br />
                        Jsme hospoda v Brně, kde to žije — od prvního piva až po poslední rundu.
                    </p>
                </div>
            </FullSection>
            <div class="block md:-mt-20 lg:-mt-20 ml-2 lg:ml-20 w-full">
                <!--                    events gallery section-->
                <H4header>…a kde to žije!</H4header>
                <div class="grid grid-cols-12">
                    <div
                        class="col-span-12 md:col-span-3 lg:col-span-2 flex justify-end md:justify-center -mt-[120px] md:mt-0"
                    >
                        <div class="block w-fit mt-5">
                            <div class="flex justify-end pr-10 md:pr-0">
                                <img
                                    src="/img/bg/sticky/samolepka-neumim-tancit.webp"
                                    class="aspect-auto w-6/12 md:w-full"
                                    alt=""
                                />
                            </div>
                        </div>
                    </div>
                    <div class="col-span-12 md:col-span-9 lg:col-span-10 mt-5">
                        <VerticalScrollCards :galleries="eventGalleries" />
                    </div>
                </div>
            </div>
            <div class="flex justify-center -mt-20">
                <Link :href="route('front.galleries')">
                    <ButtonMain>zobrazit vše</ButtonMain>
                </Link>
            </div>
        </div>

        <div class="relative overflow-hidden -mt-0.5">
            <div
                class="h-[350px] lg:h-[550px] bg-bgLight z-10 [clip-path:polygon(0_0,100%_0,100%_50%,0_2px)] xl:[clip-path:polygon(0_0,100%_0,100%_100%,0_2px)]"
            />
            <div class="absolute top-0 right-0">
                <FullSection>
                    <div class="w-full py-10">
                        <div
                            class="relative left-[200px] bottom-[20px] md:left-[150px] xl:-bottom-[100px] xl:left-[100px]"
                        >
                            <img
                                src="/img/bg/sticky/samolepka-tady-se-na-zidli.webp"
                                class="w-5/12 lg:w-6/12 xl:w-9/12 rotate-[23deg] md:rotate-[15deg] lg:rotate-[20deg]"
                                alt=""
                            />
                        </div>
                    </div>
                </FullSection>
            </div>
        </div>

        <FullSection>
            <div
                class="grid grid-cols-2 z-20 w-full -mt-[200px] md:-mt-[250px] lg:-mt-[400px] xl:-mt-[300px]"
            >
                <div class="relative w-full">
                    <H3Header>
                        Pro každého <br />
                        máme něco…
                    </H3Header>
                </div>
            </div>
        </FullSection>

        <!--        food section-->
        <div class="block -mt-[100px] md:-mt-[100px] lg:-mt-[250px] xl:-mt-[100px] z-30 ">
            <IndexPhotoBlock v-if="sections.food" v-bind="sections.food" />
        </div>

        <div class="relative overflow-hidden -mt-0.5 mt-5">
            <div
                class="h-[350px] lg:h-[450px] bg-bgLight z-10 [clip-path:polygon(0_100%,100%_50%,100%_100%)] xl:[clip-path:polygon(0_100%,100%_0%,100%_100%)]"
            />
            <div
                class="absolute bottom-[40px] md:-bottom-[20px] lg:bottom-[10px] xl:bottom-[40px] w-full"
            >
                <FullSection>
                    <div class="grid grid-cols-2 z-20 w-full md:py-10 lg:-mb-10">
                        <div class="block"></div>
                        <div class="w-full">
                            <div class="flex items-end justify-end">
                                <img
                                    src="/img/bg/sticky/samolepka-nikdo-nerikal.webp"
                                    class="w-full xl:w-10/12 -rotate-[15deg] md:-rotate-[8deg] lg:-rotate-[5deg] xl:-rotate-[8deg]"
                                    alt=""
                                />
                            </div>
                        </div>
                    </div>
                </FullSection>
            </div>
        </div>
        <div class="block bg-bgLight -mt-1 pb-32">
            <!--            drinks section-->
            <IndexPhotoBlock v-if="sections.drinks" v-bind="sections.drinks" />

            <div class="flex justify-center w-full">
                <img
                    src="/img/bg/sticky/samolepka-pod-hajkem.webp"
                    class="w-10/12 lg:w-4/12 xl:w-6/12 -rotate-[1deg]"
                    alt=""
                />
            </div>
            <FullSection>
                <div class="block -mt-[100px]">
                    <!--            birthday party-->
                    <div class="grid grid-cols-1 lg:grid-cols-12 pt-32">
                        <div
                            :class="
                                hasBirthday && !isToday
                                    ? isDetailedCountdown
                                        ? 'col-span-1 lg:col-span-7'
                                        : 'col-span-1 lg:col-span-7'
                                    : 'col-span-1 xl:col-span-12'
                            "
                        >
<div class="flex lg:mt-10 xl:-mt-5 items-center">
                            <H3Header>
                                <span class="text-3xl md:text-3xl 2xl:text-5xl">
                                Příští narozeniny<br class="md:hidden" />
                                U Sajmona pod Hájkem budou za:
                                    </span>
                            </H3Header>
</div>
                            <!-- No birthday configured -->
                            <p v-if="!hasBirthday" class="text-4xl mt-5">Datum připravujeme.</p>

                            <!-- Today: birthday is happening -->
                            <p v-else-if="isToday" class="text-4xl mt-5">
                                jsou dnes!! — dojděte už slavíme.
                            </p>
                        </div>
                        <div
                            v-if="hasBirthday && !isToday"
                            :class="[
                                'col-span-1 lg:col-span-4 mt-10 xl:mt-0 ',
                                isDetailedCountdown ? 'lg:col-span-3' : 'lg:col-span-2'
                            ]"
                        >
                            <div
                                :class="[
                                    'grid gap-8 xl:ml-10',
                                    isDetailedCountdown ? 'grid-cols-3' : 'grid-cols-2'
                                ]"
                            >
                                <CounterItem :num="displayNum1" :unit="displayUnit1" />
                                <CounterItem :num="displayNum2" :unit="displayUnit2" />
                                <CounterItem
                                    v-if="isDetailedCountdown"
                                    :num="displayNum3"
                                    :unit="displayUnit3"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </FullSection>
        </div>

        <FullSection>
            <div class="block mt-[50px]">
                <H3Header>
                    Zahrádka, oslavy i večery, <br />
                    které stojí za to
                </H3Header>
            </div>
        </FullSection>
        <!--        party section-->
        <IndexPhotoBlock v-if="sections.events" v-bind="sections.events" />

        <FullSection>
            <div class="flex justify-center pt-32">
                <img src="/img/bg/sticky/samolepka-nejsme-ovce.webp" alt="" />
            </div>
        </FullSection>

        <div class="block my-32"></div>
        <!--        party section-->
        <IndexPhotoBlock v-if="sections.weddings" v-bind="sections.weddings" />
    </div>
</template>
