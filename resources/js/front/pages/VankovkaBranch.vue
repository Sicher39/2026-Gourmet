<script setup lang="ts">
import BranchLayout from '@/front/layouts/BranchLayout.vue'
import FitTextItem from '@/front/components/FitText/FitTextItem.vue'
import FullSection from '@/front/components/Sections/FullSection.vue'
import FitTextHandWriteItem from '@/front/components/FitText/FitTextHandWriteItem.vue'
import BasicFoodMenu from '@/front/components/MenuItems/BasicFoodMenu.vue'
import { nextTick, onBeforeUnmount, onMounted, ref } from 'vue'
import { gsap } from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'
import DailyMenu from '@/front/components/MenuItems/DailyMenu.vue'
import AnimateSvgItem from '@/front/components/AnimateSvg/AnimateSvgItem.vue'
import Line4 from '@/front/components/AnimateSvg/SvgItems/Line4.vue'
import BreakfastMenu from '@/front/components/MenuItems/BreakfastMenu.vue'
import DynamicGallery from '@/front/components/FoodGallery/DynamicGallery.vue'
import CookGallery from '@/front/components/FoodGallery/CookGallery.vue'
import CompanyContacts from '@/front/components/Contacts/CompanyContacts.vue'
import Line5 from '@/front/components/AnimateSvg/SvgItems/Line5.vue'
import OpeningHours from '@/front/components/Contacts/OpeningHours.vue'

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
                const opacity = followingProgress > 0 ? 0 : 1 - progress * 0.25

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
        ],
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
        ],
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
        ],
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
        ],
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
        ],
    }
])

const breakfastMenus = ref([
    {
        menuItems: [
            {
                foodName: 'Míchaná vajíčka – vejce 3ks, anglická slanina, zelenina',
                allergens: '1, 7, 9',
                price: 169,
                enabled: true,
                menuVariants: [
                    { name: 's brokolicí a špenátem' },
                    { name: 's rajčátky a parmezánem', allergens: '7' }
                ]
            },
            {
                allergens: '1, 7, 9',
                foodName: 'Kuřecí plátek na bylinkách, jasmínová rýže',
                price: 169,
                enabled: true
            },
            {
                allergens: '1, 3, 7',
                foodName: 'Špagety Carbonara se slaninou a parmazánem',
                price: 179,
                enabled: true
            },
            {
                allergens: '1, 3, 7',
                foodName: 'Smažený květák, vařené brambory, tatarská omáčka',
                price: 159,
                enabled: true
            }
        ]
    }
])

const gourmet = ['cesar', 'coffe-01', 'coffe-02', 'cesar']

const cooksGallery = ref([
    { image: 'cook-01', name: 'Bartoloměj' },
    { image: 'cook-02', name: 'Venca' },
    { image: 'cook-03', name: 'Tonda' }
])

const companyBranches = ref([
    {
        name: 'Gourmet U Vaňkovky',
        street: 'Trnitá 500/9',
        city: '602 00 Brno-střed',
        phone: '+420 737 789 123'
    }
])

const sectionsHours = ref([
    {
        section: 'Snídaně',
        openingHours: [
            {
                days: 'po–pá',
                hours: '07.00–09:30'
            }
        ]
    },
    {
        section: 'Kavárna',
        openingHours: [
            {
                days: 'po–čt',
                hours: '07:00–14:30'
            },
            {
                days: 'Pá',
                hours: '07.00–14:00'
            }
        ]
    },
    {
        section: 'Restaurace',
        openingHours: [
            {
                days: 'po–čt',
                hours: '07.00–14:30'
            },
            {
                days: 'Pá',
                hours: '07.00–14:00'
            }
        ]
    }
])
</script>

<template>
    <FullSection id="uvod">
        <div class="block">
            <div class="relative w-full">
                <FitTextItem text="Gourmet" />
                <FitTextHandWriteItem text="U Vaňkovky" class="-mt-[350px]" />
            </div>
        </div>
    </FullSection>

    <FullSection id="denni-menu">
        <div class="block py-20">
            <h3 class="font-head text-center text-4xl font-bold">
                {{ menus[0].day }} {{ menus[0].date }}
            </h3>

            <BasicFoodMenu
                :day="menus[0].day"
                :date="menus[0].date"
                :soup-items="menus[0].soupItems"
                :menu-items="menus[0].menuItems"
                :pizza-items="menus[0].pizzaItems"
            />
        </div>
    </FullSection>
    <FullSection>
        <div id="tydenni-menu" ref="weeklyMenuSection" class="block my-48">
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
    <div class="flex justify-center -mt-[200px]">
        <AnimateSvgItem class="w-2/12 text-accent">
            <Line4 />
        </AnimateSvgItem>
    </div>

    <FullSection id="kavarna">
        <div class="block -mt-[130px]">
            <div class="relative w-full">
                <FitTextItem text="kavárna" />
                <FitTextHandWriteItem text="pro pohodová rána" class="-mt-[300px]" />
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-20 -mt-[120px]">
                <div class="block">
                    <h3 class="font-head text-primary text-6xl font-black">
                        Začněte svůj den poctivou snídaní u nás!
                    </h3>
                </div>
                <div class="block pt-[90px] space-y-10">
                    <p>
                        Naše dopolední nabídka potěší každého, kdo si potrpí na opravdu vydatné
                        snídaně z čerstvých surovin. Každé ráno pro vás připravujeme nadýchaná
                        míchaná vejce, křupavé bagety i sladké ovesné kaše.
                    </p>
                    <p>
                        K dobrému jídlu patří prémiová káva a denně čerstvá nabídka našich domácích
                        zákusků a dortů. Stavte se u nás v klidu posnídat, uspořádejte ranní
                        pracovní schůzku nebo si vezměte kávu s sebou.
                    </p>
                </div>
            </div>

            <div class="block pt-20">
                <div v-for="(menu, index) in breakfastMenus" :key="index">
                    <BreakfastMenu :menu-items="menu.menuItems" />
                </div>
            </div>
        </div>
    </FullSection>

    <DynamicGallery :images="gourmet" />

    <div class="flex justify-center -mt-[200px]">
        <AnimateSvgItem class="w-2/12 text-accent">
            <Line5 />
        </AnimateSvgItem>
    </div>

    <FullSection>
        <div class="block -mt-[130px]">
            <div class="relative w-full">
                <FitTextItem text="Kuchaři" />
                <FitTextHandWriteItem text="srdce naší restaurace" class="-mt-[300px]" />
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-20 -mt-[120px]">
                <div class="block">
                    <h3 class="font-head text-primary text-6xl font-black">
                        Kdo pro vás každý den vaří?
                    </h3>
                </div>
                <div class="block pt-[90px] space-y-10">
                    <p>
                        Věříme, že dobré jídlo se dá vařit jedině s radostí a chutí. Náš tým se
                        stará o to, aby byl váš polední oběd, stejně jako snídaně, pokaždé
                        perfektním zážitkem, kvůli kterému se k nám budete rádi vracet.
                    </p>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 w-full py-48 gap-y-20 md:gap-y-0 md:gap-5 lg:gap-20">
                <CookGallery v-for="(item, i) in cooksGallery" :key="i" v-bind="item" />
            </div>
        </div>
    </FullSection>

    <FullSection id="kontakt">
        <FitTextHandWriteItem text="těšíme se na Vás" class="" />
    </FullSection>

    <FullSection>
        <div class="grid grid-cols-2 md:grid-cols-4 py-5 border-t-1 border-accent md:divide-x-1 divide-y-1 md:divide-y-0 md:divide-x-0 divide-accent">
            <CompanyContacts v-for="(item, i) in companyBranches" :key="i" v-bind="item" />
            <OpeningHours v-for="(item, i) in sectionsHours" :key="i" v-bind="item" />
        </div>
    </FullSection>
</template>
