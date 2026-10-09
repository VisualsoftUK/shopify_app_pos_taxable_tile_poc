import '@shopify/polaris/build/esm/styles.css';
import '@admin-css/utilities.css';
import { createInertiaApp, router } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createRoot } from 'react-dom/client';
import { I18nProvider } from '@admin/Components/Common/I18nProvider';
import i18n from 'i18next';
import {
    createI18nInstance,
    createAppTranslationLoader,
    LOCALE_CONFIG,
} from '@admin/Services/Common/I18n';

// Create app translation loader
const loadAppTranslations = createAppTranslationLoader(LOCALE_CONFIG);

const appName = import.meta.env.VITE_APP_NAME || 'App Template';

// Session token handling for Shopify (REQUIRED)
router.on('before', (event) => {
    // If token exists, let the visit proceed
    if (window.sessionToken) {
        return;
    }
    
    // Token not ready - cancel this visit
    event.preventDefault();

    setTimeout(() => {
        router.visit(event.detail.visit.url, {
            method: event.detail.visit.method,
            data: event.detail.visit.data,
            headers: event.detail.visit.headers,
            preserveState: event.detail.visit.preserveState,
            preserveScroll: event.detail.visit.preserveScroll,
            only: event.detail.visit.only,
            async: event.detail.visit.async,
        });
    }, 50);
});

// Use Inertia's router hooks to inject Shopify session token on every request
// Note: window.sessionToken is maintained by token_handler.blade.php
router.on('start', (event) => {
    // Inject the Authorization header with the session token for all Inertia requests
    if (window.sessionToken) {
        event.detail.visit.headers = {
            ...event.detail.visit.headers,
            'Authorization': `Bearer ${window.sessionToken}`,
        };
    }
});

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            [
                `./src/Pages/${name}.jsx`,
                `../../vendor/visualsoft/vs-mantle/resources/js/src/Pages/${name}.jsx`,
            ],
            import.meta.glob([
                './src/Pages/**/*.jsx',
                '../../vendor/visualsoft/vs-mantle/resources/js/src/Pages/*.jsx',
            ]),
        ),
    async setup({ el, App, props }) {
        const root = createRoot(el);
        
        // Initialize i18n instance using admin package utility
        const i18nInstance = await createI18nInstance(i18n, loadAppTranslations);

        root.render(
            <I18nProvider i18n={i18nInstance}>
                <App {...props} />
            </I18nProvider>
        ); 
    },
    progress: {
        color: '#4B5563',
    },
});
