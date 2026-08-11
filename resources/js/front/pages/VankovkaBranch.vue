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

interface Cook {
    id: number
    image: string
    name: string
}

interface CompanyContact {
    name: string
    street: string
    city: string
    phone: string
    email: string
}

interface OpeningHour {
    days: string
    hours: string
}

interface OpeningHoursSection {
    section: string
    openingHours: OpeningHour[]
}

const props = withDefaults(defineProps<{
    galleryImages?: string[]
    cooks?: Cook[]
    companyBranch?: CompanyContact[]
    openingHours?: OpeningHoursSection[]
}>(), {
    galleryImages: () => [
        '/img/actions/cesar.webp',
        '/img/actions/coffe-01.webp',
        '/img/actions/coffe-02.webp',
        '/img/actions/cesar.webp'
    ],
    cooks: () => [],
    companyBranch: () => [],
    openingHours: () => []
})

const weeklyMenuSection = ref<HTMLElement | null>(null)
let weeklyMenuContext: gsap.Context | null = null
let removeWeeklyMenuResizeListener: (() => void) | null = null
let removeWeeklyMenuScrollListener: (() => void) | null = null
let removeWeeklyMenuItemsScrollListener: (() => void) | null = null

onMounted(async (): Promise<void> => {
    await nextTick()
    await document.fonts.ready

    const section = weeklyMenuSection.value

    if (!section) {
        return
    }

    const cards = Array.from(section.querySelectorAll<HTMLElement>('.weekly-menu-card'))
    const cardContents = cards
        .map((card) => card.querySelector<HTMLElement>('.weekly-menu-card-surface'))
        .filter((card): card is HTMLElement => card instanceof HTMLElement)
    const itemViewports = cards
        .map((card) => card.querySelector<HTMLElement>('.weekly-menu-items-viewport'))
        .filter((viewport): viewport is HTMLElement => viewport instanceof HTMLElement)
    const menuItems = cards
        .map((card) => card.querySelector<HTMLElement>('.weekly-menu-items'))
        .filter((items): items is HTMLElement => items instanceof HTMLElement)
    const overflowReadingDistance = 120
    let stickyTop = 80

    const updateOverflowingMenuItems = (): void => {
        cards.forEach((card, index) => {
            const cardContent = cardContents[index]
            const itemViewport = itemViewports[index]
            const items = menuItems[index]

            if (!cardContent || !itemViewport || !items) {
                return
            }

            const overflowHeight = Math.max(0, items.scrollHeight - itemViewport.clientHeight)

            if (overflowHeight === 0) {
                items.style.transform = ''

                return
            }

            const scrollDistance = stickyTop - card.getBoundingClientRect().top
            const itemOffset = Math.min(overflowHeight, Math.max(0, scrollDistance))

            items.style.transform = `translate3d(0, -${itemOffset}px, 0)`
        })
    }

    const synchronizeCardHeights = (): void => {
        stickyTop = window.matchMedia('(min-width: 768px)').matches ? 100 : 55

        cards.forEach((card) => {
            card.style.height = ''
            card.style.marginBottom = ''
            card.style.paddingTop = ''
            card.style.position = ''
        })
        cardContents.forEach((cardContent) => {
            cardContent.style.height = ''
            cardContent.style.top = ''
        })
        itemViewports.forEach((viewport) => {
            viewport.style.height = ''
        })
        menuItems.forEach((items) => {
            items.style.transform = ''
        })

        const availableCardHeight = Math.max(320, window.innerHeight - stickyTop - 32)
        const tallestNaturalHeight = Math.max(
            ...cardContents.map((cardContent) => cardContent.scrollHeight)
        )
        const visualCardHeight = Math.min(tallestNaturalHeight, availableCardHeight)

        cardContents.forEach((cardContent) => {
            cardContent.style.height = `${visualCardHeight}px`
            cardContent.style.top = `${stickyTop}px`
        })

        itemViewports.forEach((viewport, index) => {
            const cardContent = cardContents[index]

            if (!cardContent) {
                return
            }

            const cardRect = cardContent.getBoundingClientRect()
            const viewportRect = viewport.getBoundingClientRect()
            const viewportHeight = Math.max(
                120,
                visualCardHeight - (viewportRect.top - cardRect.top) - 40
            )

            viewport.style.height = `${viewportHeight}px`
        })

        cards.forEach((card, index) => {
            const itemViewport = itemViewports[index]
            const items = menuItems[index]

            if (!itemViewport || !items) {
                return
            }

            const overflowHeight = Math.max(0, items.scrollHeight - itemViewport.clientHeight)
            const readingDistance = overflowHeight > 0 ? overflowReadingDistance : 0
            const isLastCard = index === cards.length - 1

            card.style.position = 'relative'
            card.style.height = `${
                visualCardHeight +
                overflowHeight +
                readingDistance +
                (isLastCard ? 0 : visualCardHeight)
            }px`

            if (!isLastCard) {
                card.style.marginBottom = `-${visualCardHeight}px`
            }
        })

        updateOverflowingMenuItems()
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
    window.addEventListener('scroll', updateOverflowingMenuItems, { passive: true })
    removeWeeklyMenuResizeListener = () => window.removeEventListener('resize', handleResize)
    removeWeeklyMenuItemsScrollListener = () =>
        window.removeEventListener('scroll', updateOverflowingMenuItems)

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
            const shrinkingDistance = card.clientHeight / 2
            const nextCardTop = nextCard.getBoundingClientRect().top
            const overlapPastHalf = stickyTop + shrinkingDistance - nextCardTop

            return Math.min(1, Math.max(0, overlapPastHalf / shrinkingDistance))
        }

        const updateCardScales = (): void => {
            cardContents.forEach((cardContent, index) => {
                const nextCardContent = cardContents[index + 1]
                const followingCardContent = cardContents[index + 2]
                const setScaleX = scaleXSetters[index]
                const setScaleY = scaleYSetters[index]
                const setOpacity = opacitySetters[index]

                if (!nextCardContent || !setScaleX || !setScaleY || !setOpacity) {
                    return
                }

                const progress = getOverlapProgress(cardContent, nextCardContent)
                const followingProgress = followingCardContent
                    ? getOverlapProgress(nextCardContent, followingCardContent)
                    : 0
                const scale = 1 - progress * 0.1
                const isFullyCovered = progress >= 1 || followingProgress > 0
                const opacity = isFullyCovered ? 0 : 1 - progress

                cardContent.style.visibility = isFullyCovered ? 'hidden' : 'visible'
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
    removeWeeklyMenuItemsScrollListener?.()
    removeWeeklyMenuItemsScrollListener = null
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
            },
            {
                menuIndex: 5,
                allergens: '1, 7',
                weight: '130',
                foodName: 'Kuřecí prsíčka na pomerančích, jasmínová rýže',
                price: 169,
                enabled: true
            },
            {
                menuIndex: 6,
                allergens: '1, 7',
                weight: '130',
                foodName: 'Kuřecí prsíčka na pomerančích, jasmínová rýže',
                price: 169,
                enabled: true
            },
            {
                menuIndex: 7,
                allergens: '1, 7',
                weight: '130',
                foodName: 'Kuřecí prsíčka na pomerančích, jasmínová rýže',
                price: 169,
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

const gourmet = props.galleryImages

const cooksGallery = props.cooks

const companyBranches = props.companyBranch
const sectionsHours = props.openingHours
</script>

<template>
    <FullSection id="uvod">
        <div class="block">
            <div class="relative w-full mt-32 md:mt-10">
                <FitTextItem text="Gourmet" />
                <FitTextHandWriteItem text="U Vaňkovky" class="-mt-20 lg:-mt-48 2xl:-mt-[350px]" />
            </div>
        </div>
    </FullSection>

    <FullSection id="denni-menu">
        <div class="block">
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
        <div id="tydenni-menu" ref="weeklyMenuSection" class="block md:mb-48 xl:my-48">
            <div class="relative w-full">
                <FitTextHandWriteItem text="Týdenní menu" class="" />
            </div>
            <div
                v-for="(menu, index) in menus"
                :key="menu.day"
                :style="{ zIndex: index + 1 }"
                class="weekly-menu-card relative"
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
    <div class="flex justify-center md:-mt-[200px]">
        <AnimateSvgItem class="w-5/12 lg:w-2/12 text-accent">
            <Line4 />
        </AnimateSvgItem>
    </div>

    <FullSection id="kavarna">
        <div class="block md:-mt-[70px] xl:-mt-[70px] 3xl:-mt-[130px]">
            <div class="relative w-full">
                <FitTextItem text="kavárna" />
                <FitTextHandWriteItem
                    text="pro pohodová rána"
                    class="-mt-[70px] md:-mt-[150px] xl:-mt-[240px] 2xl:-mt-[270px] 3xl:-mt-[300px]"
                />
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-20 2xl:-mt-[120px]">
                <div class="block">
                    <h3
                        class="font-head text-primary text-3xl md:text-6xl lg:text-4xl 2xl:text-6xl font-black"
                    >
                        Začněte svůj den poctivou snídaní u nás!
                    </h3>
                </div>
                <div class="block xl space-y-10 lg:pt-10">
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

            <div class="block md:pt-20">
                <div v-for="(menu, index) in breakfastMenus" :key="index">
                    <BreakfastMenu :menu-items="menu.menuItems" />
                </div>
            </div>
        </div>
    </FullSection>

    <div class="block -mt-48">
        <DynamicGallery :images="gourmet" />
    </div>

    <div class="flex justify-center -mt-20 md:-mt-[200px]">
        <AnimateSvgItem class="w-5/12 lg:w-2/12 text-accent">
            <Line5 />
        </AnimateSvgItem>
    </div>

    <FullSection v-if="cooksGallery.length > 0">
        <div class="block -mt-[40px] md:-mt-[70px] 2xl:-mt-[110px] 3xl:-mt-[130px]">
            <div class="relative w-full">
                <FitTextItem text="Kuchaři" />
                <FitTextHandWriteItem
                    text="srdce naší pobočky"
                    class="-mt-[70px] md:-mt-[120px] lg:-mt-[160px] xl:-mt-[200px] 3xl:-mt-[300px]"
                />
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-20 3xl:-mt-[120px]">
                <div class="block">
                    <h3
                        class="font-head text-primary md:text-6xl lg:text-4xl 2xl:text-6xl font-black"
                    >
                        Kdo se o vás stará U&nbsp;Vaňkovky?
                    </h3>
                </div>
                <div class="block lg:pt-10 xl:pt-[90px] space-y-10">
                    <p>
                        U Vaňkovky pro vás vaří a servíruje sehraná parta profíků, která dbá na
                        perfektní čerstvost a detail každého talíře. Postarají se o to, aby měl váš
                        polední oběd i ranní káva skvělé tempo, stálou chuť a přátelskou atmosféru.
                    </p>
                </div>
            </div>

            <div
                class="grid grid-cols-1 md:grid-cols-3 w-full py-20 md:py-48 gap-y-20 md:gap-y-0 md:gap-5 lg:gap-20"
            >
                <CookGallery v-for="item in cooksGallery" :key="item.id" v-bind="item" />
            </div>
        </div>
    </FullSection>

    <FullSection id="kontakt">
        <FitTextHandWriteItem text="těšíme se na Vás" class="" />
    </FullSection>

    <FullSection>
        <div
            class="grid grid-cols-2 md:grid-cols-4 py-5 border-t-1 border-accent md:divide-x-1 md:divide-x-0 divide-accent"
        >
            <CompanyContacts v-for="(item, i) in companyBranches" :key="i" v-bind="item" />
            <OpeningHours v-for="(item, i) in sectionsHours" :key="i" v-bind="item" />
        </div>
    </FullSection>
</template>
