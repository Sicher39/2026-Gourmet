export interface DailySoupItem {
    soupIndex: number
    allergens: string
    weight: string
    unit: string
    soupName: string
    price: string
    enabled: boolean
}

export interface DailyFoodItem {
    menuIndex: number
    allergens: string
    weight: string
    unit: string
    foodName: string
    price: string
    enabled: boolean
}

export interface DailyPizzaItem {
    menuIndex: number
    allergens: string
    weight: string
    unit: string
    pizzaName: string
    price: string
    enabled: boolean
}

export interface DailyGrillItem {
    menuIndex: number
    allergens: string
    weight: string
    unit: string
    grillName: string
    price: string
    enabled: boolean
}

export interface BranchMenuDay {
    day: string
    date: string
    isNonCookingDay: boolean
    nonCookingMessage: string | null
    soupItems: DailySoupItem[]
    menuItems: DailyFoodItem[]
    pizzaItems: DailyPizzaItem[]
    grillItems: DailyGrillItem[]
}

export interface BranchMenuPayload {
    today: BranchMenuDay
    upcoming: BranchMenuDay[]
}

export interface BreakfastMenuVariant {
    name: string
    allergens: string
}

export interface BreakfastMenuItem {
    foodName: string
    allergens: string
    price: string
    enabled: boolean
    menuVariants: BreakfastMenuVariant[]
}

export interface BreakfastMenuPayload {
    items: BreakfastMenuItem[]
}
