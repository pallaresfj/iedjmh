import {
    defineConfig
} from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: {
                app: 'resources/css/app.css',
                public: 'resources/css/public.css',
                'filament-admin': 'resources/css/filament/admin/theme.css',
                main: 'resources/js/app.js',
            },
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        cors: true,
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
