export type NavLink = {
    title: string
    link: string
    route: string
}

export const navLinks: NavLink[] = [
    {
        title: 'Úvod',
        link: '/',
        route: 'front.index'
    },
    {
        title: 'Jídelní lístek',
        link: '/jidelni-listek',
        route: 'front.foodMenu'
    },
    {
        title: 'Nápojový lístek',
        link: '/napojovy-listek',
        route: 'front.drinkMenu'
    },
    {
        title: 'Párty gelerie',
        link: '/galerie',
        route: 'front.galleries'
    },
]
