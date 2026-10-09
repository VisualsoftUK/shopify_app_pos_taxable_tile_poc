import { defineConfig } from 'vite';
import shopify from 'vite-plugin-shopify';

export default defineConfig({
    plugins: [
        shopify(),
    ],
    server: {
        port: 5174,
        strictPort: true,
    },
});
