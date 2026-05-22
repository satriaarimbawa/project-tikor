import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/css/login.css', 'resources/css/dash_operator.css'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
            host: '0.0.0.0', // Mengizinkan akses dari IP mana pun
        hmr: {
            host: 'localhost',
        }
        },
    },
});
