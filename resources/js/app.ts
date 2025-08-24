import { createApp, h, DefineComponent } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { ZiggyVue } from 'ziggy-js'
import Vue3Autocounter from 'vue3-autocounter'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import VueDatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css'

// If Ziggy is globally available (Laravel + Ziggy), declare it:
declare const Ziggy: any

const appName = 'Evermar Calculator'

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./Pages/**/*.vue')
        ),
    setup({el, App, props, plugin}) {
        createApp({render: () => h(App, props)})
            .use(plugin)
            .use(ZiggyVue, Ziggy) // ✅ Ziggy typing handled via declaration
            .component('vue3-autocounter', Vue3Autocounter)
            .component('VueDatePicker', VueDatePicker)
            .mount(el)
    },
    progress: {
        color: '#00a63e',
    },
})
