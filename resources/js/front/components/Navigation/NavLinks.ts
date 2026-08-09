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
        anchor: 'uvod',
        menus: ['main']
    },
    {
        title: 'úvod',
        anchor: 'uvod',
        menus: ['branch']
    },
    {
        title: 'Ponávka',
        link: '/gourmet-ponavka',
        route: 'front.ponavka-branch',
        menus: ['main']
    },
    {
        title: 'U Vaňkovky',
        link: '/gourmet-u-vankovky',
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

    /*Ponávka a Vaňkovka*/
    {
        title: 'Denní menu',
        anchor: 'denni-menu',
        menus: ['branch']
    },
    {
        title: 'Týdenní menu',
        anchor: 'tydenni-menu',
        menus: ['branch']
    },
    {
        title: 'Kavárna',
        anchor: 'kavarna',
        menus: ['branch']
    },
    {
        title: 'Kontakt',
        anchor: 'kontakt',
        menus: ['main']
    },
    {
        title: 'Kontakt',
        anchor: 'kontakt',
        menus: ['branch']
    },
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
