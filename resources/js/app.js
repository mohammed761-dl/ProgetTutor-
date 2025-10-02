import "./bootstrap";
import { createApp, h } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";
import { router } from "@inertiajs/vue3";
import "../css/app.css";

// Make route function available globally in Vue components
window.route =
    window.route ||
    function () {
        console.error("Route function not loaded");
    };

// Configure Inertia to include CSRF token in requests
router.on("before", (event) => {
    const token = document.head.querySelector(
        'meta[name="csrf-token"]'
    )?.content;
    if (token) {
        event.detail.visit.headers = {
            ...event.detail.visit.headers,
            "X-CSRF-TOKEN": token,
        };
    }
});

createInertiaApp({
    resolve: (name) => {
        const pages = import.meta.glob("./Pages/**/*.vue", { eager: true });
        return pages[`./Pages/${name}.vue`];
    },
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });
        app.use(plugin);
        app.config.globalProperties.route = window.route;
        app.mount(el);
    },
});
