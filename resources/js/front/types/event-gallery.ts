export interface GalleryPhoto {
    url: string
    alt?: string
    width?: number
    height?: number
}

export interface EventGalleryData {
    id: number
    title: string
    eventDate: string | null
    dateLabel: string | null
    coverImageUrl: string | null
    photos: GalleryPhoto[]
}
