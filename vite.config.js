import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/consumer.css',
                'resources/css/admin.css',
                'resources/css/sidebar.css',
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/consumer.js',
                'resources/js/admin.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
