<script lang="ts" setup>
import MainLayout from '@/front/layouts/MainLayout.vue'
import FullSection from '@/front/components/Sections/FullSection.vue'
import HeaderTextItem from '@/front/components/HeaderTextPictureSection/HeaderTextItem.vue'
import { ref } from 'vue'
import AnimateSvgItem from '@/front/components/AnimateSvg/AnimateSvgItem.vue'
import Line1 from '@/front/components/AnimateSvg/SvgItems/Line1.vue'
import FitTextItem from '@/front/components/FitText/FitTextItem.vue'
import FitTextHandWriteItem from '@/front/components/FitText/FitTextHandWriteItem.vue'
import Line2 from '@/front/components/AnimateSvg/SvgItems/Line2.vue'
import DynamicGallery from '@/front/components/FoodGallery/DynamicGallery.vue'
import Line3 from '@/front/components/AnimateSvg/SvgItems/Line3.vue'
import ButtonMain from '@/front/components/Buttons/ButtonMain.vue'
import CookGallery from '@/front/components/FoodGallery/CookGallery.vue'
import CompanyContacts from '@/front/components/Contacts/CompanyContacts.vue'
import DeliveryItem from '@/front/Deliveries/DeliveryItem.vue'

interface DeliveryService {
    id: number
    img: string
    alt: string
    branch: string
    link: string
}

interface Cook {
    id: number
    image: string
    name: string
}

interface GalleryImages {
    'gourmet-1': string[]
    'gourmet-2': string[]
}

defineOptions({
    layout: MainLayout,
    inheritAttrs: false
})

const places = ref([
    { header: 'Ponávka', link: 'front.ponavka-branch' },
    { header: 'U\u00A0Vaňkovky', link: 'front.vankovka-branch' }
])

interface CompanyContact {
    name: string
    street: string
    city: string
    phone: string
    email: string
    companyNumber?: string
    vat?: string
    bankAccount?: string
    dataBox?: string
    justice?: string
}

const props = withDefaults(
    defineProps<{
        company?: CompanyContact[]
        companyBranch?: CompanyContact[]
        deliveryServices?: DeliveryService[]
        galleryImages?: GalleryImages
        cooks?: Cook[]
    }>(),
    {
        company: () => [],
        companyBranch: () => [],
        deliveryServices: () => [],
        cooks: () => [],
        galleryImages: () => ({
            'gourmet-1': [
                '/img/actions/cesar.webp',
                '/img/actions/coffe-01.webp',
                '/img/actions/coffe-02.webp',
                '/img/actions/cesar.webp'
            ],
            'gourmet-2': [
                '/img/actions/cesar.webp',
                '/img/actions/coffe-01.webp',
                '/img/actions/coffe-02.webp',
                '/img/actions/cesar.webp'
            ]
        })
    }
)

const company = props.company
const companyBranches = props.companyBranch

const deliveries = props.deliveryServices
const cooksGallery = props.cooks
const gourmetOne = props.galleryImages['gourmet-1']
const gourmetTwo = props.galleryImages['gourmet-2']
</script>

<template>
    <!--    main header-->
    <FullSection id="uvod">
        <div
            class="grid grid-cols-1 md:grid-cols-2 gap-y-20 md:gap-y-0 md:gap-5 lg:gap-20 pt-[50px] md:pt-[70px] 2xl:pt-[100px]"
        >
            <HeaderTextItem
                v-for="(item, i) in places"
                :key="i"
                :image="i % 2 === 0"
                :link="item.link"
                :header="item.header"
            />
        </div>
    </FullSection>

    <FullSection>
        <div class="flex justify-center">
            <AnimateSvgItem
                class="w-6/12 lg:w-5/12 xl:w-3/12 text-accent md:-mt-5 lg:-mt-32 xl:-mt-10"
            >
                <Line1 />
            </AnimateSvgItem>
        </div>
    </FullSection>

    <FullSection id="rozvoz">
        <div class="block -mt-[40px] md:-mt-16 lg 3xl:-mt-32">
            <div class="relative w-full md:mb-32">
                <FitTextItem text="Rozvoz" />
                <FitTextHandWriteItem
                    text="každý pracovní den"
                    class="-mt-[70px] md:-mt-[150px] lg:-mt-[170px] xl:-mt-[220px] 3xl:-mt-[350px]"
                />
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-20 md:-mt-[100px] 3xl:-mt-[210px]">
                <div class="block">
                    <h3
                        class="font-head text-primary text-3xl md:text-6xl lg:text-3xl 3xl:text-6xl font-black"
                    >
                        Máte chuť na něco dobrého z našeho menu?
                    </h3>
                </div>
                <div class="block lg:pt-10 3xl:pt-[90px] space-y-10">
                    <p>
                        Objednejte si bleskový rozvoz přes Bolt Food nebo Foodoru přímo k vám domů
                        či do kanceláře.
                    </p>
                    <p class="font-bold">
                        Pondělí–čtvrtek: 10.45–14.15 <br />
                        Pátek: 10.45–13.45
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-y-20 md:gap-y-0">
                        <DeliveryItem v-for="(item, i) in deliveries" :key="i" v-bind="item" />
                    </div>
                </div>
            </div>
        </div>
    </FullSection>

    <DynamicGallery :images="gourmetOne" />

    <FullSection id="catering">
        <div class="block">
            <div class="relative w-full">
                <FitTextItem text="Catering" />
                <FitTextHandWriteItem
                    text="bez starostí"
                    class="-mt-[70px] md:-mt-[180px] xl:-mt-[220px] 3xl:-mt-[350px]"
                />
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-20 3xl:-mt-[280px]">
                <div class="block">
                    <h3
                        class="font-head text-primary text-3xl md:text-6xl lg:text-3xl 3xl:text-6xl font-black"
                    >
                        Plánujete firemní večírek, oslavu nebo svatbu?
                    </h3>
                    <div class="hidden lg:flex justify-end">
                        <AnimateSvgItem class="w-8/12 text-accent">
                            <Line2 />
                        </AnimateSvgItem>
                    </div>
                </div>
                <div class="block lg:pt-[40px] 3xl:pt-[90px] space-y-10">
                    <p>
                        Kompletně pro vás zajistíme rauty, bankety, recepce či školení včetně
                        doprovodného programu, jako je živá hudba, barmanská show nebo živé vaření.
                    </p>
                    <p>
                        Akci rádi uspořádáme v prostorách naší restaurace a kavárny, případně
                        kdekoliv jinde dle vašich požadavků. Umíme malé akce pro 5 osob i velké
                        události pro více než 100 hostů.
                    </p>
                    <div class="flex justify-end">
                        <ButtonMain>kontaktovat</ButtonMain>
                    </div>
                    <div class="flex justify-start lg:hidden -mt-32">
                        <AnimateSvgItem class="w-8/12 -scale-x-100 text-accent">
                            <Line2 />
                        </AnimateSvgItem>
                    </div>
                </div>
            </div>
        </div>
    </FullSection>
    <FullSection id="rauty">
        <div class="block -mt-[40px] md:-mt-[80px] 3xl:-mt-[200px]">
            <div class="relative w-full">
                <FitTextItem text="Rauty" />
                <FitTextHandWriteItem
                    text="podle vašich představ"
                    class="-mt-[70px] md:-mt-[150px] lg:-mt-[170px] xl:-mt-[260px] 3xl:-mt-[350px]"
                />
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-20 md:mt-10 3xl:-mt-[120px]">
                <div class="block">
                    <h3
                        class="font-head text-primary text-3xl md:text-6xl lg:text-3xl 3xl:text-6xl font-black"
                    >
                        Pohoštění na schůzku nebo rodinné setkání?
                    </h3>
                </div>
                <div class="block lg:pt-[40px] 3xl:pt-[90px] space-y-10">
                    <p>
                        Připravíme pro vás čerstvé bagety, chlebíčky, dezerty i bohaté ovocné,
                        zeleninové či slané mísy. Na zakázku pro vás rádi uvaříme i teplé obědy ve
                        větším počtu porcí.
                    </p>
                    <p>
                        Vše pro vás nachystáme k vyzvednutí u nás v domluvený čas. Vzhledem k
                        zakázkové výrobě přijímáme tyto objednávky nejpozději
                        <span class="font-bold">5 pracovních dní předem.</span>
                    </p>
                    <div class="flex justify-end">
                        <ButtonMain>kontaktovat</ButtonMain>
                    </div>
                </div>
            </div>
        </div>
    </FullSection>

    <DynamicGallery :images="gourmetTwo" />

    <div class="flex justify-center -mt-32 md:-mt-[200px]">
        <AnimateSvgItem class="w-5/12 md:w-2/12 lg:w-1/12 xl:w-2/12 3xl:w-2/12 text-accent">
            <Line3 />
        </AnimateSvgItem>
    </div>

    <FullSection v-if="cooksGallery.length > 0">
        <div class="block -mt-[40px] md:-mt-[70px] 2xl:-mt-[110px] 3xl:-mt-[130px]">
            <div class="relative w-full">
                <FitTextItem text="Kuchaři" />
                <FitTextHandWriteItem
                    text="srdce naší restaurace"
                    class="-mt-[70px] md:-mt-[120px] lg:-mt-[160px] xl:-mt-[200px] 3xl:-mt-[300px]"
                />
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-20 3xl:-mt-[120px]">
                <div class="block">
                    <h3
                        class="font-head text-primary md:text-6xl lg:text-4xl 2xl:text-6xl font-black"
                    >
                        Kdo pro vás každý den vaří?
                    </h3>
                </div>
                <div class="block lg:pt-10 xl:pt-[90px] space-y-10">
                    <p>
                        Věříme, že dobré jídlo se dá vařit jedině s radostí a chutí. Náš tým se
                        stará o to, aby byl váš polední oběd, stejně jako snídaně, pokaždé
                        perfektním zážitkem, kvůli kterému se k nám budete rádi vracet.
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
            class="grid grid-cols-1 md:grid-cols-3 py-5 border-t-1 border-accent md:divide-x-1 divide-y-1 md:divide-y-0 md:divide-x-0 divide-accent"
        >
            <CompanyContacts v-for="(item, i) in company" :key="i" v-bind="item" />
            <CompanyContacts v-for="(item, i) in companyBranches" :key="i" v-bind="item" />
        </div>
    </FullSection>
</template>
