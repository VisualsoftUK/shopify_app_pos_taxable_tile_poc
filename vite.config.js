import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import { defineConfig } from 'vite';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel(['resources/js/app.jsx']),
        react(),
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js/src'),
            '@css': path.resolve(__dirname, 'resources/css'),
            '@js': path.resolve(__dirname, 'resources/js'),
            '@layouts': path.resolve(__dirname, 'resources/js/src/Layouts'),
            '@components': path.resolve(__dirname, 'resources/js/src/Components'),
            '@services': path.resolve(__dirname, 'resources/js/src/Services'),
            '@skeletons': path.resolve(__dirname, 'resources/js/src/Skeletons'),
            '@files': path.resolve(__dirname, 'resources/js/src/Files'),
            '@locales': path.resolve(__dirname, 'locales'),
            '@mantle': path.resolve(__dirname, 'vendor/visualsoft/vs-mantle/resources/js/src'),
            '@mantle-locales': path.resolve(__dirname, 'vendor/visualsoft/vs-mantle/locales'),
            '@app-pricing': path.resolve(__dirname, 'vendor/visualsoft/vs-shopify-app-pricing/resources/js/src'),
            '@app-pricing-locales': path.resolve(__dirname, 'vendor/visualsoft/vs-shopify-app-pricing/locales'),
            '@admin': path.resolve(__dirname, 'vendor/visualsoft/vs-shopify-admin/resources/js/src'),
            '@admin-css': path.resolve(__dirname, 'vendor/visualsoft/vs-shopify-admin/resources/css'),
            '@admin-locales': path.resolve(__dirname, 'vendor/visualsoft/vs-shopify-admin/locales'),
            '@beacons': path.resolve(__dirname, 'vendor/visualsoft/vs-shopify-beacons/resources'),
        },
    },
});
