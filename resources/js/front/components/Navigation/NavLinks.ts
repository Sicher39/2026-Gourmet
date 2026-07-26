export type NavigationMenu = 'main' | 'branch'

export type NavLink = {
    title: string
    link?: string
    route?: string
    anchor?: string
    menus: NavigationMenu[]
}

export const navLinks: NavLink[] = [
    {
        title: 'úvod',
        link: '/',
        route: 'front.index',
        menus: ['main']
    },
    {
        title: 'Ponávka',
        link: '/ponavka',
        route: 'front.ponavka-branch',
        menus: ['main']
    },
    {
        title: 'U Vaňkovky',
        link: '/u-vankovky',
        route: 'front.vankovka-branch',
        menus: ['main']
    },

    // Společné menu obou poboček a kotvy
    /*index*/
    {
        title: 'Rozvoz',
        anchor: 'rozvoz',
        menus: ['main']
    },
    {
        title: 'Catering',
        anchor: 'catering',
        menus: ['main']
    },
    {
        title: 'Rauty',
        anchor: 'rauty',
        menus: ['main']
    },
    {
        title: 'Kontakt',
        anchor: 'kontakt',
        menus: ['main']
    },

    /*Ponávka a Vaňkovka*/
    {
        title: 'denní menu',
        anchor: 'denni-menu',
        menus: ['branch']
    },
    {
        title: 'káva',
        anchor: 'kava',
        menus: ['branch']
    },
    {
        title: 'čaj',
        anchor: 'caj',
        menus: ['branch']
    }
]

export function getNavLinks(menu: NavigationMenu): NavLink[] {
    return navLinks.filter((link) => link.menus.includes(menu))
}

export function getNavLinkUrl(navLink: NavLink): string {
    const link = navLink.link ?? ''

    if (!navLink.anchor) {
        return link
    }

    return `${link}#${navLink.anchor}`
}
