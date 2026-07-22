export type PricelistPrice = {
    id: number
    label: string
    audience?: string | null
    discountedPrice?: string | null
    standardPrice?: string | null
    note?: string | null
    highlighted?: boolean
}

export type PricelistPriceGroup = {
    id: number
    title: string
    subtitle?: string | null
    rows: PricelistPrice[]
}

export type PricelistItem = {
    id: number
    slug: string
    title: string
    subtitle?: string | null
    description?: string | null
    audiences: string[]
    durations: string[]
    prices: PricelistPrice[]
    priceGroups?: PricelistPriceGroup[]
    discountedPeriod: string
    standardPeriod: string
    validFrom?: string | null
    validTo?: string | null
    note?: string | null
    ctaLabel?: string | null
    order: number
}

export type SharedPricelistLinkedFee = {
    id: number
    title: string
    formattedValue: string
}

export type SharedPricelistInfoItem = {
    id: number
    text: string
    linkedFee?: SharedPricelistLinkedFee | null
}

export type SharedPricelistInfo = {
    id: number
    title: string
    text: string | null
    items?: SharedPricelistInfoItem[]
}
