import '../../css/app.css';
import '../bootstrap.ts';
import { createApp, h, type DefineComponent } from 'vue';
import { createInertiaApp, router } from '@inertiajs/vue3';
import { ZiggyVue } from 'ziggy-js';
import { transformPageProps } from './utils/czechTypography';

createInertiaApp({
    resolve: async (name) => {
        const pages = import.meta.glob<{ default: DefineComponent }>('./pages/**/*.vue');
        const page = pages[`./pages/${name}.vue`];

        if (!page) {
            throw new Error(`Page not found: ${name}`);
        }

        return (await page()).default;
    },
    setup({ el, App, props, plugin }) {
        // Apply Czech typography to the initial page props before mounting.
        transformPageProps(props.initialPage.props);

        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
});

// Apply Czech typography to page props after every successful Inertia navigation.
router.on('success', (event) => {
    transformPageProps(event.detail.page.props);
});
