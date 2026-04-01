import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

import { cloudflare } from "@cloudflare/vite-plugin";

export default defineConfig({
    plugins: [laravel({
        input: ['resources/css/app.css', 'resources/js/app.js', 'resources/css/login.css', 'resources/css/dash_operator.css'],
        refresh: true,
    }), tailwindcss(), cloudflare()],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});