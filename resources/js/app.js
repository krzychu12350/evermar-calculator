import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { ZiggyVue } from 'ziggy-js'
import Vue3Autocounter from 'vue3-autocounter';

const appName = 'Evermar Calculator';

createInertiaApp({
    title: (title) => `${appName}`,
    resolve: name => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true })
        const page = pages[`./Pages/${name}.vue`]
        if (!page) {
            throw new Error(`Page ${name} not found.`)
        }
        return page.default
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue, Ziggy) // ✅ register Ziggy
            .component('vue3-autocounter', Vue3Autocounter)
            .mount(el)
    },
    progress: {
        color: '#00a63e'
    },
})
