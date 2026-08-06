<script setup lang="ts">
import BranchLayout from '@/front/layouts/BranchLayout.vue'
import FitTextItem from '@/front/components/FitText/FitTextItem.vue'
import FullSection from '@/front/components/Sections/FullSection.vue'
import FitTextHandWriteItem from '@/front/components/FitText/FitTextHandWriteItem.vue'
import BasicFoodMenu from '@/front/components/MenuItems/BasicFoodMenu.vue'
import {computed, nextTick, onBeforeUnmount, onMounted, ref} from 'vue'
import { gsap } from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'
import DailyMenu from '@/front/components/MenuItems/DailyMenu.vue'

defineOptions({
    layout: BranchLayout,
    inheritAttrs: false
})

const weeklyMenuSection = ref<HTMLElement | null>(null)
let weeklyMenuContext: gsap.Context | null = null
let removeWeeklyMenuResizeListener: (() => void) | null = null
let removeWeeklyMenuScrollListener: (() => void) | null = null

onMounted(async (): Promise<void> => {
    await nextTick()
    await document.fonts.ready

    const section = weeklyMenuSection.value

    if (!section) {
        return
    }

    const cards = Array.from(section.querySelectorAll<HTMLElement>('.weekly-menu-card'))
    const cardContents = cards
        .map((card) => card.firstElementChild)
        .filter((card): card is HTMLElement => card instanceof HTMLElement)

    const synchronizeCardHeights = (): void => {
        cardContents.forEach((cardContent) => {
            cardContent.style.height = ''
        })

        const tallestCardHeight = Math.max(
            ...cardContents.map((cardContent) => cardContent.scrollHeight)
        )

        cardContents.forEach((cardContent) => {
            cardContent.style.height = `${tallestCardHeight}px`
        })
    }

    synchronizeCardHeights()

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches
    const handleResize = (): void => {
        synchronizeCardHeights()

        if (!prefersReducedMotion) {
            ScrollTrigger.refresh()
        }
    }

    window.addEventListener('resize', handleResize)
    removeWeeklyMenuResizeListener = () => window.removeEventListener('resize', handleResize)

    if (prefersReducedMotion) {
        return
    }

    gsap.registerPlugin(ScrollTrigger)

    weeklyMenuContext = gsap.context(() => {
        cardContents.forEach((cardContent) => {
            gsap.set(cardContent, {
                opacity: 1,
                scaleX: 1,
                scaleY: 1,
                transformOrigin: 'top center'
            })
        })

        const scaleXSetters = cardContents.map((cardContent) =>
            gsap.quickTo(cardContent, 'scaleX', {
                duration: 0.18,
                ease: 'power1.out'
            })
        )
        const scaleYSetters = cardContents.map((cardContent) =>
            gsap.quickTo(cardContent, 'scaleY', {
                duration: 0.18,
                ease: 'power1.out'
            })
        )
        const opacitySetters = cardContents.map((cardContent) =>
            gsap.quickTo(cardContent, 'opacity', {
                duration: 0.18,
                ease: 'power1.out'
            })
        )

        const getOverlapProgress = (card: HTMLElement, nextCard: HTMLElement): number => {
            const cardRect = card.getBoundingClientRect()
            const nextCardRect = nextCard.getBoundingClientRect()
            const shrinkingDistance = cardRect.height / 2
            const overlapPastHalf = cardRect.top + shrinkingDistance - nextCardRect.top

            return Math.min(1, Math.max(0, overlapPastHalf / shrinkingDistance))
        }

        const updateCardScales = (): void => {
            cards.forEach((card, index) => {
                const nextCard = cards[index + 1]
                const followingCard = cards[index + 2]
                const setScaleX = scaleXSetters[index]
                const setScaleY = scaleYSetters[index]
                const setOpacity = opacitySetters[index]

                if (!nextCard || !setScaleX || !setScaleY || !setOpacity) {
                    return
                }

                const progress = getOverlapProgress(card, nextCard)
                const followingProgress = followingCard
                    ? getOverlapProgress(nextCard, followingCard)
                    : 0
                const scale = 1 - progress * 0.1
                const opacity = followingProgress > 0
                    ? 0
                    : 1 - progress * 0.25

                setScaleX(scale)
                setScaleY(scale)
                setOpacity(opacity)
            })
        }

        window.addEventListener('scroll', updateCardScales, { passive: true })
        removeWeeklyMenuScrollListener = () =>
            window.removeEventListener('scroll', updateCardScales)

        updateCardScales()
    }, section)
})

onBeforeUnmount((): void => {
    removeWeeklyMenuResizeListener?.()
    removeWeeklyMenuResizeListener = null
    removeWeeklyMenuScrollListener?.()
    removeWeeklyMenuScrollListener = null
    weeklyMenuContext?.revert()
    weeklyMenuContext = null
})

const menus = ref([
    {
        day: 'Pondělí',
        date: '22. 6. 2026',

        soupItems: [
            {
                soupIndex: 1,
                allergens: '1, 3, 9',
                weight: '330',
                soupName: 'Hovězí vývar s masem, zeleninou a nudlemi',
                price: 39,
                enabled: true
            }
        ],

        menuItems: [
            {
                menuIndex: 1,
                allergens: '1, 3, 7',
                weight: '150',
                foodName: 'Vepřový řízek, vařené brambory, citron',
                price: 169,
                enabled: true
            },
            {
                menuIndex: 2,
                allergens: '1, 7, 9',
                weight: '150',
                foodName: 'Kuřecí plátek na bylinkách, jasmínová rýže',
                price: 169,
                enabled: true
            },
            {
                menuIndex: 3,
                allergens: '1, 3, 7',
                weight: '350',
                foodName: 'Špagety Carbonara se slaninou a parmazánem',
                price: 179,
                enabled: true
            },
            {
                menuIndex: 4,
                allergens: '1, 3, 7',
                weight: '350',
                foodName: 'Smažený květák, vařené brambory, tatarská omáčka',
                price: 159,
                enabled: true
            }
        ]
    },

    {
        day: 'Úterý',
        date: '23. 6. 2026',

        soupItems: [
            {
                soupIndex: 1,
                allergens: '1, 7',
                weight: '330',
                soupName: 'Bramborový krém s krutony',
                price: 39,
                enabled: true
            }
        ],

        menuItems: [
            {
                menuIndex: 1,
                allergens: '1, 3, 7',
                weight: '150',
                foodName: 'Moravský vrabec, dušený špenát, bramborový knedlík',
                price: 179,
                enabled: true
            },
            {
                menuIndex: 2,
                allergens: '1, 7, 9',
                weight: '150',
                foodName: 'Kuřecí prsa na žampionech, dušená rýže',
                price: 169,
                enabled: true
            },
            {
                menuIndex: 3,
                allergens: '1, 3, 7',
                weight: '350',
                foodName: 'Zapečené těstoviny se šunkou a sýrem, okurkový salát',
                price: 159,
                enabled: true
            },
            {
                menuIndex: 4,
                allergens: '1, 3, 7',
                weight: '150',
                foodName: 'Smažený eidam, hranolky, tatarská omáčka',
                price: 179,
                enabled: true
            }
        ]
    },

    {
        day: 'Středa',
        date: '24. 6. 2026',

        soupItems: [
            {
                soupIndex: 1,
                allergens: '1, 9',
                weight: '330',
                soupName: 'Čočková polévka s uzeninou',
                price: 39,
                enabled: true
            },
            {
                soupIndex: 2,
                allergens: '7, 9',
                weight: '330',
                soupName: 'Zeleninový krém se zakysanou smetanou',
                price: 45,
                enabled: true
            }
        ],

        menuItems: [
            {
                menuIndex: 1,
                allergens: '1, 7, 9',
                weight: '150',
                foodName: 'Hovězí na červeném víně, bramborová kaše',
                price: 189,
                enabled: true
            },
            {
                menuIndex: 2,
                allergens: '1, 3, 7',
                weight: '150',
                foodName: 'Kuřecí řízek v kukuřičné strouhance, šťouchané brambory',
                price: 179,
                enabled: false
            },
            {
                menuIndex: 3,
                allergens: '1, 3, 7',
                weight: '350',
                foodName: 'Bramborové noky se špenátem, kuřecím masem a smetanou',
                price: 179,
                enabled: true
            },
            {
                menuIndex: 4,
                allergens: '1, 7',
                weight: '350',
                foodName: 'Zeleninové rizoto s parmazánem',
                price: 159,
                enabled: true
            }
        ]
    },

    {
        day: 'Čtvrtek',
        date: '25. 6. 2026',

        soupItems: [
            {
                soupIndex: 1,
                allergens: '1, 3, 9',
                weight: '330',
                soupName: 'Kuřecí vývar s masem, zeleninou a kapáním',
                price: 39,
                enabled: true
            }
        ],

        menuItems: [
            {
                menuIndex: 1,
                allergens: '1, 3, 7',
                weight: '150',
                foodName: 'Svíčková na smetaně, houskový knedlík, brusinky',
                price: 189,
                enabled: true
            },
            {
                menuIndex: 2,
                allergens: '1, 7, 9',
                weight: '150',
                foodName: 'Vepřová panenka, pepřová omáčka, americké brambory',
                price: 199,
                enabled: true
            },
            {
                menuIndex: 3,
                allergens: '1, 3, 7',
                weight: '350',
                foodName: 'Lasagne Bolognese zapečené se sýrem',
                price: 179,
                enabled: true
            },
            {
                menuIndex: 4,
                allergens: '1, 3, 7',
                weight: '350',
                foodName: 'Brokolicové placičky, vařené brambory, bylinkový dip',
                price: 159,
                enabled: true
            }
        ]
    },

    {
        day: 'Pátek',
        date: '26. 6. 2026',

        soupItems: [
            {
                soupIndex: 1,
                allergens: '1, 3, 9',
                weight: '330',
                soupName: 'Hovězí vývar s masem a nudlemi',
                price: 39,
                enabled: true
            }
        ],

        menuItems: [
            {
                menuIndex: 1,
                allergens: '1, 3, 7',
                weight: '130',
                foodName: 'Moravský vrabec, dušený špenát, bramborový knedlík',
                price: 169,
                enabled: true
            },
            {
                menuIndex: 2,
                allergens: '1, 7',
                weight: '130',
                foodName: 'Kuřecí prsíčka na pomerančích, jasmínová rýže',
                price: 169,
                enabled: false
            },
            {
                menuIndex: 3,
                allergens: '1, 3, 7',
                weight: '150',
                foodName: 'Smažený řízek, bramborový salát, citron',
                price: 179,
                enabled: false
            },
            {
                menuIndex: 4,
                allergens: '1, 3, 7',
                weight: '140',
                foodName: 'Smažený karbanátek, bramborová kaše, okurka',
                price: 169,
                enabled: true
            }
        ]
    }
])


</script>

<template>
    <FullSection>
        <div class="block">
            <div class="relative w-full">
                <FitTextItem text="Gourmet" />
                <FitTextHandWriteItem text="U Vaňkovky" class="-mt-[350px]" />
            </div>
        </div>
    </FullSection>

    <FullSection>
        <div class="block py-20">
            <h3 class="font-head text-center text-4xl font-bold">{{ menus[0].day }} {{ menus[0].date}}</h3>

            <BasicFoodMenu
                :day="menus[0].day"
                :date="menus[0].date"
                :soup-items="menus[0].soupItems"
                :menu-items="menus[0].menuItems"
            />
        </div>
    </FullSection>
    <FullSection>
        <div ref="weeklyMenuSection" class="block my-48">
            <div class="relative w-full">
                <FitTextHandWriteItem text="Týdenní menu" class="" />
            </div>
            <div
                v-for="(menu, index) in menus"
                :key="menu.day"
                :style="{ zIndex: index + 1 }"
                class="weekly-menu-card sticky top-20 md:top-48"
            >
                <DailyMenu
                    :day="menu.day"
                    :date="menu.date"
                    :second="index % 2 !== 0"
                    :soup-items="menu.soupItems"
                    :menu-items="menu.menuItems"
                />
            </div>
        </div>
    </FullSection>
    <div aria-hidden="true" class="min-h-screen"></div>
</template>
