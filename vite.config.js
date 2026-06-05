import {
    defineConfig
} from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        // Use IPv4 — Windows browsers often fail to load assets from http://[::1]:5173
        host: '127.0.0.1',
        port: 5173,
        cors: true,
        hmr: {
            host: '127.0.0.1',
        },
    },
});