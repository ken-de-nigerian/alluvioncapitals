import "../css/app.css";
import { createApp, h } from "vue";
import { createInertiaApp, Head, Link } from "@inertiajs/vue3";
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from "ziggy-js";

import 'bootstrap/dist/js/bootstrap.bundle.min';
import 'bootstrap-icons/font/bootstrap-icons.css';

// Dynamic imports for layouts
const MainLayout = () => import('./Shared/Layouts/MainLayout.vue');
const AuthLayout = () => import('./Shared/Layouts/AuthLayout.vue');
const AdminLayout = () => import('./Shared/Layouts/AdminLayout.vue');
const UserLayout = () => import('./Shared/Layouts/UserLayout.vue');

// Dynamic imports for components
const QuillEditor = () => import('./Components/Editor/QuillEditor.vue');
const DatePicker = () => import('./Components/Forms/DatePicker.vue');
const LoadingButton = () => import('./Components/Button/LoadingButton.vue');
const GLightbox = () => import('./Components/Utilities/Glightbox.js');

// Handle the returned Promise properly
const app = createInertiaApp({
    title: title => `${title}`,
    resolve: async (name) => {
        const page = await resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue', { eager: false })
        );

        // Set the layout based on a route pattern
        if (name.startsWith('Auth/') || ['Login', 'Register', 'ForgotPassword'].includes(name)) {
            page.default.layout = page.default.layout || (await AuthLayout()).default;
        } else if (name.startsWith('Admin/')) {
            page.default.layout = page.default.layout || (await AdminLayout()).default;
        } else if (name.startsWith('User/')) {
            page.default.layout = page.default.layout || (await UserLayout()).default;
        } else {
            page.default.layout = page.default.layout || (await MainLayout()).default;
        }

        return page;
    },
    setup({ el, App, props, plugin }) {
        const vueApp = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .component("Head", Head)
            .component("Link", Link)
            .component('LoadingButton', LoadingButton)
            .component('QuillEditor', QuillEditor)
            .component('DatePicker', DatePicker)
            .component('GLightbox', GLightbox);

        vueApp.mount(el);

        // Return the app instance
        return vueApp;
    },
    progress: {
        color: '#f2223b',
        showSpinner: true,
    },
});

// Handle any potential errors from the Promise
app.catch(error => {
    console.error('Inertia app initialization failed:', error);
});
